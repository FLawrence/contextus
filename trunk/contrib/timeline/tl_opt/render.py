import pygame

class ScreenRenderer(object):
	stripes = 3 # Number of character stripes across the display
	ystep = 15 # Pixels stride to use between characters
	stripe_gap = 3 # Number of ysteps between stripes
	char_width = 4 # Width of a character world-line
	event_size = 10 # Size of the blob representing an event
	margin = 10
	
	def __init__(self, characters, events):
		self.events = events
		self.chars = characters

		pygame.init()
		size = width, height = (1280, 800)
		self.surface = pygame.display.set_mode(size, pygame.DOUBLEBUF)

		# Assign colours to the characters
		if len(self.chars) <= 8:
			col_layers = 1
			divs = len(self.chars)
		elif 8 < len(self.chars) <= 16:
			col_layers = 2
			divs = len(self.chars)/2
		else:
			col_layers = 2
			divs = 8

		lightness = 50
		col_layer = 0
		col_idx = 0

		for ch in self.chars.itervalues():
			if col_layer >= 2:
				ch.colour = pygame.Color(255, 255, 255, 0)
			else:
				ch.colour = pygame.Color(0, 0, 0, 0)
				ch.colour.hsla = (360*col_idx/divs, 100, lightness, 0)
				col_idx += 1
				if col_idx >= divs:
					col_idx = 0
					col_layer += 1
					lightness = 85

	def draw(self):
		self.surface.fill((0, 0, 0))

		# Horizontal separation:
		xstep = (self.surface.get_width() - self.margin) * self.stripes \
				/ (len(self.events) + 1)

		# Underlay the event markers
		xpos = self.margin + xstep
		yblock = 0
		for ev in self.events:
			ypos = self.margin + yblock
			last_ce = None
			for ce in ev.chars:
				if ce.link:
					coords = (xpos, ypos)
					pygame.draw.circle(
						self.surface, (255, 255, 255, 100),
						coords,	self.event_size, 1)
					if last_ce is not None and last_ce.link:
						pygame.draw.rect(
							self.surface,
							(0, 0, 0, 100),
							(coords[0] - self.event_size, last_coords[1],
							 self.event_size*2, coords[1] - last_coords[1]))
						pygame.draw.line(
							self.surface,
							(255, 255, 255, 100),
							(coords[0] - self.event_size, coords[1]),
							(last_coords[0] - self.event_size, last_coords[1]))
						pygame.draw.line(
							self.surface,
							(255, 255, 255, 100),
							(coords[0] + self.event_size, coords[1]),
							(last_coords[0] + self.event_size, last_coords[1]))
					last_coords = coords
				ypos = ypos + self.ystep
				last_ce = ce
			xpos += xstep
			if xpos > self.surface.get_width() - self.margin:
				xpos = self.margin
				yblock += (len(self.chars) + self.stripe_gap) * self.ystep

		# Draw each character line one at a time
		for ch in self.chars.itervalues():
			ypos = self.margin
			last_xpos = self.margin
			last_ypos = self.margin
			last_ce = ch.events[0]
			
			for ce in ch.events:
				xpos = last_xpos + xstep
				if xpos > self.surface.get_width() - self.margin:
					xpos = self.margin
					ypos = last_ypos + self.ystep * (len(self.chars) + self.stripe_gap)
				else:
					coords = (xpos, ypos + self.ystep * ce.index)
					pygame.draw.line(
						self.surface, ch.colour,
						(last_xpos, last_ypos + self.ystep * last_ce.index),
						coords,
						self.char_width)
				
				last_xpos = xpos
				last_ypos = ypos
				last_ce = ce

		pygame.display.flip()

	def save(self, basename):
		pygame.image.save(self.surface, basename + ".jpg")
