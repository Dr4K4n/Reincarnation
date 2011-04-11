#!/usr/bin/python
# -*- coding: utf-8 -*-

from time import time
import MySQLdb

def make_dict(buildings):
	dict = {}
	array = buildings.split('\n')
	try:
		array.remove('')
	except:
		pass
	for eintrag in array:
		zeile = eintrag.split(';')
		dict[int(zeile[0])] = int(zeile[1])
	return dict

points_dict = {}
now = int(time())
oneweekago = now-7*24*60*60

verbindung = MySQLdb.connect('127.0.0.1','reincarnation','HCBjBW5XRUEEfA8w')
cursor = verbindung.cursor()

cursor.execute('USE reincarnation;')

# alte Nachrichten löschen
cursor.execute('DELETE FROM messages WHERE time <= %s'%oneweekago)

# alte Berichte löschen
cursor.execute('DELETE FROM reports WHERE time <= %s'%oneweekago)

# Punktzahlen die die Gebï¿½ude pro Stufe geben aus der DB lesen
cursor.execute('SELECT id,points FROM buildings ORDER BY id')
buildings_data = cursor.fetchall()
buildings_points_dict = {}
for eintrag in buildings_data:
	buildings_points_dict[int(eintrag[0])] = int(eintrag[1])
	
# Punktzahlen die die Forschungen pro Stufe geben aus der DB lesen
cursor.execute('SELECT id,points FROM researches ORDER BY id')
researches_data = cursor.fetchall()
researches_points_dict = {}
for eintrag in researches_data:
	researches_points_dict[int(eintrag[0])] = int(eintrag[1])

# Punkte aller Stï¿½dte berechnen, in die DB eintragen und den Besitzern gutschreiben
cursor.execute('SELECT id,owner,buildings FROM cities')
city_buildings = cursor.fetchall()

for eintrag in city_buildings:
	city_points = 0
	city_id = eintrag[0]
	owner_id = eintrag[1]
	buildings = eintrag[2]
	buildings_dict = make_dict(buildings)
	for i in buildings_dict:
		try:
			building_points = buildings_dict[i]*buildings_points_dict[i]
			city_points += int(building_points)
		except:
			pass
	try:
		points_dict[owner_id] += int(city_points)
	except:
		points_dict[owner_id] = int(city_points)
		
	cursor.execute("UPDATE cities SET `points`='%s' WHERE `id`='%s'" % (city_points,city_id))

# Forschungspunkte aller Spieler berechnen und in die DB eintragen
cursor.execute('SELECT id,research FROM gamer')
gamer_researches = cursor.fetchall()
researcher_dict = {}

for gamer in gamer_researches:
	researches_points = 0
	gamer_id = gamer[0]
	research = gamer[1]
	research_dict = make_dict(research)
	for i in research_dict:
		try:
			research_points = research_dict[i]*researches_points_dict[i]
			researches_points += int(research_points)
		except:
			pass
	researcher_dict[gamer_id] = researches_points
	cursor.execute("UPDATE gamer SET `research_points`='%s' WHERE `id`='%s'" %(researches_points,gamer_id))

# Gebï¿½udepunkte aller Spieler in die DB eintragen
for x in points_dict:
	try:
		points = points_dict[x]+researcher_dict[x]
	except:
		points = points_dict[x]
	cursor.execute("UPDATE gamer SET `buildings_points`='%s',`points`='%s' WHERE `id`='%s'" %(points_dict[x],points,x))

datei = file("lastupdate.txt","w")
datei.write(str(int(time())))