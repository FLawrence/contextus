# -*- coding: utf-8 -*-

import sys
import random
import math

import pygame

from tl_opt.datastruct import Character, Event, ChEv
from tl_opt.render import ScreenRenderer

class ObjVal(object):
	def __init__(self, grouping=0, cross=0, spread=0):
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

	def __iadd__(self, other):
		self.grouping += other.grouping
		self.cross += other.cross
		self.spread += other.spread
		return self

	def __add__(self, other):
		return ObjVal(self.grouping + other.grouping,
					  self.cross + other.cross,
					  self.spread + other.spread)

	def __sub__(self, other):
		return ObjVal(self.grouping - other.grouping,
					  self.cross - other.cross,
					  self.spread - other.spread)

	def better(self):
		if self.grouping < 0:
			return True
		elif self.grouping == 0:
			if self.cross < 0:
				return True
			elif self.cross == 0:
				if self.spread < 0:
					return True
		return False


def find_crossings(before, after):
	"""Count the number of crossings when going from the "before"
	sequence of characters to the "after" sequence."""
	total = 0
	for x, (xb, xa) in enumerate(zip(before, after)):
		for y, (yb, ya) in enumerate(zip(before, after)):
			if y <= x:
				continue
			if (yb.index > xb.index and ya.index < xa.index
				or yb.index < xb.index and ya.index > xa.index):
				total += 1
	return total

class Objective(object):
	def __init__(self, grouping):
		self.grouping = grouping

	def __call__(self, chars, events):
		return self.full(chars, events)

	def full(self, chars, events):
		event_grouping = 0
		crossings = []
		last_ev = None

		result = ObjVal()
	
		for ev in events:
			if last_ev is not None:
				pobj = self.partial(last_ev, ev)
				result += pobj

			last_ev = ev

		pobj = self.partial(last_ev, None)
		result += pobj

		return result

	def partial(self, ev1, ev2):
		"""Compute the objective function contribution for adjacent events
		ev1, ev2. If ev2 is None, then ev1 is the last event.
		"""
		event_grouping = 0
		crossings = 0
		crossing_spread = 0

		event_grouping = ev1.get_grouping_badness()
		if ev2 is not None:
			crossings = find_crossings(ev1.corder, ev2.corder)
			if crossings > 1:
				crossing_spread = (crossings-1)*(crossings-1)

		if not self.grouping:
			event_grouping *= 3
			event_grouping += crossings

		return ObjVal(event_grouping, crossings, crossing_spread)


def swap_move(events, e, i, obj):
	orig_obj = ObjVal()
	if e < len(events)-1:
		orig_obj += obj.partial(events[e], events[e+1])
	if e > 0:
		orig_obj += obj.partial(events[e-1], events[e])
	
	event = events[e]
	event.chars[i], event.chars[i+1] = event.chars[i+1], event.chars[i]
	event.chars[i].index = i
	event.chars[i+1].index = i+1

	new_obj = ObjVal()
	if e < len(events)-1:
		new_obj += obj.partial(events[e], events[e+1])
	if e > 0:
		new_obj += obj.partial(events[e-1], events[e])
	return new_obj - orig_obj

def swap_unmove(events, e, i):
	event = events[e]
	event.chars[i], event.chars[i+1] = event.chars[i+1], event.chars[i]
	event.chars[i].index = i
	event.chars[i+1].index = i+1

def insert_move(events, e, i, j, obj=None):
	if obj is not None:
		orig_obj = ObjVal()
		if e < len(events)-1:
			orig_obj += obj.partial(events[e], events[e+1])
		if e > 0:
			orig_obj += obj.partial(events[e-1], events[e])

	event = events[e]
	if j >= i:
		j += 1
	event.chars.insert(j, event.chars.pop(i))
	event.renumber()

	if obj is not None:
		new_obj = ObjVal()
		if e < len(events)-1:
			new_obj += obj.partial(events[e], events[e+1])
		if e > 0:
			new_obj += obj.partial(events[e-1], events[e])
		return new_obj - orig_obj
	else:
		return 0

def insert_unmove(events, e, i, j):
	event = events[e]
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

def improve_step_insert_best_improve(chars, events, objective):
	"""Moves here are inserts -- pick a character at a given
	event, and reinsert it into the list for this event somewhere
	else. Take the best improving result.
	"""
	current_objective = objective(chars, events)
	best_objective = current_objective
	best_move = None

	evlist = range(0, len(events))
	random.shuffle(evlist)
	for evi in evlist:
		ev = events[evi]
		ilist = range(0, len(ev.chars))
		random.shuffle(ilist)
		for i in ilist:
			jlist = range(0, len(ev.chars)-1)
			random.shuffle(jlist)
			for j in jlist:
				if j == i or j == i-1:
					continue
				# Do the move and test it
				obj_diff = insert_move(events, evi, i, j, objective)
				if obj_diff.better():
					new_objective = current_objective + obj_diff
					best_objective = new_objective
					best_move = (evi, i, j)
				insert_unmove(events, evi, i, j)

	if best_move is not None:
		insert_move(events, *best_move)
		print "I Keeping move: ", current_objective, "->", best_objective, events[best_move[0]], best_move[1], best_move[2]
		return (False, best_objective)

	print "No improving moves found"

	return (True, current_objective)

def improve_step_swap_first_improve(chars, events, objective):
	current_objective = objective(chars, events)
	evlist = range(0, len(events))
	random.shuffle(evlist)
	for evi in evlist:
		ev = events[evi]
		ilist = range(0, len(ev.chars)-1)
		random.shuffle(ilist)
		for i in ilist:
			obj_diff = swap_move(events, evi, i, objective)
			if obj_diff.better():
				new_objective = current_objective + obj_diff
				print "S Keeping move: ", current_objective, "->", new_objective, ev, i
				return (False, new_objective)
			else:
				swap_unmove(events, evi, i)

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
	first = True
	move_counter = 0

	improve_steps = ((improve_step_block_swap_first_improve, 100),
					 (improve_step_swap_first_improve, 30),
					 (improve_step_insert_best_improve, 10))
	improve_step, max_moves = improve_steps[0]
	is_counter = 0

	cycle_start = None

	while cycle_start is None or objective_value < cycle_start:
		if is_counter == 0:
			if not first and random.random() < 0.3:
				objective_function = Objective(False)
				print "Cycle start: not grouping"
			else:
				objective_function = Objective(True)
				print "Cycle start: grouping"
			cycle_start = objective_function(characters, events)

		while not complete:
			complete, objective_value = improve_step(characters, events, objective_function)
			move_counter += 1
			if move_counter >= max_moves:
				complete = True

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
						pre_obj = objective_function(characters, events)
						insert_move(events, int(ev), int(ch1), int(ch2))
						post_obj = objective_function(characters, events)
						print "Objective change:", pre_obj, "->", post_obj
			renderer.draw()
			time_step = clock.tick(100) # frames/sec
		
		move_counter = 0
		is_counter = (is_counter + 1) % len(improve_steps)
		improve_step, max_moves = improve_steps[is_counter]
		complete = False
		first = False

	renderer.save("image")
