# -*- coding: utf-8 -*-

import sys
import random
import math

import pygame

from tl_opt.datastruct import Character, Event, ChEv
from tl_opt.render import ScreenRenderer

class ObjVal(object):
	def __init__(self, grouping, cross, spread):
		self.grouping = grouping
		self.cross = cross
		self.spread = spread

	def __str__(self):
		return str((self.grouping, self.cross, self.spread))

	def __le__(self, other):
		if self.grouping != other.grouping:
			return self.grouping <= other.grouping
		if self.cross != other.cross:
			return self.cross <= other.cross
		return self.spread <= other.spread

	def __lt__(self, other):
		if self.grouping != other.grouping:
			return self.grouping < other.grouping
		if self.cross != other.cross:
			return self.cross < other.cross
		return self.spread < other.spread		


def objective_function(chars, events):
	event_grouping = 0
	crossings = []
	last_chars = None

	for ev in events:
		event_grouping += ev.get_grouping_badness()

		if last_chars is not None:
			crossings.append(0)
			for a, b in zip(ev.corder, last_chars):
				crossings[-1] += abs(a.index - b.index)

		last_chars = ev.corder

	total_crossings = 0
	crossing_spread = 0
	last_crossing = None

	for i, c in enumerate(crossings):
		total_crossings += c
		if c > 0:
			if last_crossing is not None:
				gap = len(crossings) - (i - last_crossing)
				crossing_spread += gap * gap
			crossing_spread += len(crossings) * len(crossings) * (c - 1)
			last_crossing = i
#		if c > 1:
#			crossing_spread += (c-1)*(c-1)

	return ObjVal(event_grouping, total_crossings, math.sqrt(crossing_spread))


def swap_move(event, i):
	event.chars[i], event.chars[i+1] = event.chars[i+1], event.chars[i]
	event.renumber()
	#event.chars[i].index = i
	#event.chars[i+1].index = i+1

swap_unmove = swap_move

def insert_move(event, i, j):
	if j >= i:
		j += 1
	event.chars.insert(j, event.chars.pop(i))
	event.renumber()

def insert_unmove(event, i, j):
	if j >= i:
		j += 1
	event.chars.insert(i, event.chars.pop(j))
	event.renumber()

def block_swap_move(events, e, i, downwards):
	f = e + 1
	ch = events[e].chars[i].char
	while f < len(events) and events[f].chars[i].char is ch:
		f += 1
	if downwards:
		j = i+1
	else:
		j = i-1
	for evi in range(e, f):
		event = events[evi]
		event.chars[i], event.chars[j] = event.chars[j], event.chars[i]
		event.renumber()
	return f

def block_swap_unmove(events, e, f, i, downwards):
	if downwards:
		j = i+1
	else:
		j = i-1
	for evi in range(e, f):
		event = events[evi]
		event.chars[i], event.chars[j] = event.chars[j], event.chars[i]
		event.renumber()		

def improve_step_insert_first_improve(chars, events, objective):
	"""Moves here are inserts -- pick a character at a given
	event, and reinsert it into the list for this event somewhere
	else. Take the first improving result.
	"""
	current_objective = objective(chars, events)

	evlist = events[:]
	random.shuffle(evlist)
	for ev in evlist:
		ilist = range(0, len(ev.chars))
		random.shuffle(ilist)
		for i in ilist:
			jlist = range(0, len(ev.chars)-1)
			random.shuffle(jlist)
			for j in jlist:
				if j == i or j == i-1:
					continue
				# Do the move and test it
				insert_move(ev, i, j)
				new_objective = objective(chars, events)
				if current_objective < new_objective:
					insert_unmove(ev, i, j)
				else:
					print "I Keeping move: ", current_objective, "->", new_objective, ev, i, j
					return (False, new_objective)

	return (True, current_objective)

def improve_step_swap_first_improve(chars, events, objective):
	current_objective = objective(chars, events)
	evlist = events[:]
	random.shuffle(evlist)
	for ev in evlist:
		ilist = range(0, len(ev.chars)-1)
		random.shuffle(ilist)
		for i in ilist:
			swap_move(ev, i)
			new_objective = objective(chars, events)
			if current_objective < new_objective:
				swap_unmove(ev, i)
			else:
				print "S Keeping move: ", current_objective, "->", new_objective, ev, i
				return (False, new_objective)

	return (True, current_objective)

def improve_step_block_swap_first_improve(chars, events, objective):
	current_objective = objective(chars, events)
	evlist = list(enumerate(events[:]))
	random.shuffle(evlist)
	for e, ev in evlist:
		ilist = range(0, len(ev.chars))
		random.shuffle(ilist)
		for i in ilist:
			if e != 0 and events[e].chars[i].char is events[e-1].chars[i].char:
				continue
			if i < len(ev.chars)-1:
				f = block_swap_move(events, e, i, True)
				new_objective = objective(chars, events)
				if current_objective < new_objective:
					block_swap_unmove(events, e, f, i, True)
				else:
					print "BS Keeping move: ", current_objective, "->", new_objective, e, "-", f, i, True
					return (False, new_objective)
			if i > 0:
				f = block_swap_move(events, e, i, False)
				new_objective = objective(chars, events)
				if current_objective < new_objective:
					block_swap_unmove(events, e, f, i, False)
				else:
					print "BS Keeping move: ", current_objective, "->", new_objective, e, "-", f, i, False
					return (False, new_objective)


	return (True, current_objective)

def main(argv=None):
	if argv == None:
		argv = sys.argv

	characters = {}
	events = []

	# Read in our data
	inf = open("characters.csv", "r")
	for line in inf:
		data = line.strip().split(",")
		ch = Character(*data)
		characters[ch.uri] = ch
	inf.close()

	inf = open("events.csv", "r")
	for line in inf:
		data = line.strip().split(",")
		ev = Event(*data)
		if events and ev.same_chars_as(events[-1]):
			events[-1].name += "," + ev.name
		else:
			events.append(ev)
			ev.link_chars(characters)

	clock = pygame.time.Clock()
	renderer = ScreenRenderer(characters, events)
	complete = False
	move_counter = 0

	improve_steps = ((improve_step_swap_first_improve, 30),
			 (improve_step_insert_first_improve, 10),
			 (improve_step_block_swap_first_improve, 100))
	improve_step, max_moves = improve_steps[0]
	is_counter = 0
	flip_counter = 0

	cycle_start = None

	while True:
		#pygame.event.pump()
		complete, objective_value = improve_step(characters, events, objective_function)
		move_counter += 1
		if complete:
			flip_counter += 1
		if complete or move_counter >= max_moves and flip_counter < 20:
			move_counter = 0
			is_counter = (is_counter + 1) % len(improve_steps)
			if is_counter == 0:
				if cycle_start is not None and cycle_start <= objective_value:
					break
				cycle_start = objective_value
			improve_step, max_moves = improve_steps[is_counter]
			complete = False

		pyg_events = pygame.event.get()
		for pyg_ev in pyg_events:
			if pyg_ev.type == pygame.KEYDOWN:
				if pyg_ev.dict['unicode'] == u"q":
					break
				elif pyg_ev.dict['unicode'] == u"s":
					sys.stdin.flush()
					print "Swap: event char"
					line = sys.stdin.readline().strip()
					print "Read: ", line
					ev, ch = line.split(" ")
					ev = events[int(ev)]
					pre_obj = objective_function(characters, events)
					swap_move(ev, int(ch))
					post_obj = objective_function(characters, events)
					print "Objective change:", pre_obj, "->", post_obj
				elif pyg_ev.dict['unicode'] == u"i":
					sys.stdin.flush()
					print "Insert: event char pos"
					line = sys.stdin.readline().strip()
					print "Read: ", line
					ev, ch1, ch2 = line.split(" ")
					ev = events[int(ev)]
					pre_obj = objective_function(characters, events)
					insert_move(ev, int(ch1), int(ch2))
					post_obj = objective_function(characters, events)
					print "Objective change:", pre_obj, "->", post_obj
		renderer.draw()
		time_step = clock.tick(100) # frames/sec

	renderer.save("image")