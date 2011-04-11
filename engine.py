#!/usr/bin/python
# -*- coding: utf-8 -*-

from time import time,sleep
import MySQLdb

def string_to_dict(startstring,count):
	enddict = {}
	startdict = {}
	if startstring != '':
		start = startstring.split('\n')
		for entry in start:
			entry = entry.strip()
			if entry != '':
				key = int(entry.split(';')[0])
				value = int(entry.split(';')[1])
				startdict[key] = value
	for x in range(count):
		enddict[x+1] = 0
	for id in startdict:
		enddict[id] = startdict[id]
	return enddict

def dict_to_string(dict):
	string = ''
	for id in dict:
		string += str(id)+";"+str(int(dict[id]))+"\n"
	return string.strip().replace(',','')

def orderbyid(liste):
	neueliste = {}
	highest_id = 1
	for unterliste in liste:
		neueliste[unterliste[0]] = unterliste[1:]
		highest_id = unterliste[0]
	return neueliste,highest_id

# Verbindung zum MySQL Server herstellen und Datenbank auswählen
verbindung = MySQLdb.connect('127.0.0.1','reincarnation','HCBjBW5XRUEEfA8w')
cursor = verbindung.cursor()
cursor.execute('USE reincarnation;')

# Einheitendaten einlesen
cursor.execute('SELECT id,att,deff,duration,space FROM units ORDER BY id')
units = cursor.fetchall()
units,units_count = orderbyid(units)

# Gebaeudedaten einlesen
cursor.execute('SELECT id,name,prod FROM buildings ORDER BY id')
names_buildings = cursor.fetchall()
names_buildings,buildings_count = orderbyid(names_buildings)

# Forschungsdaten einlesen
cursor.execute('SELECT id,name FROM researches')
names_research = cursor.fetchall()
names_research,researches_count = orderbyid(names_research)

# Variablen definieren, damit das script als daemon läuft
unendlich = 1
pointoftime = int(time())
nexttime = pointoftime

while unendlich == 1:
	pointoftime = int(time())
	# Wenn seit der letzen Ausführung des Scripts mind. 1 sek vergangen ist wird es wieder ausgeführt
	if pointoftime >= nexttime:
		print 'Tick: '+str(pointoftime)
		############################################################
		# Truppenevents bearbeiten #################################
		############################################################
		cursor.execute('SELECT * FROM events_troops WHERE arrive <= %s'%pointoftime)
		truppen = cursor.fetchall()
		
		if len(truppen) != 0:
			for truppe in truppen:
				print "Aktuelles Event: "+str(truppe)
				id = truppe[0]
				source = truppe[1]
				source_owner = truppe[2]
				target = truppe[3]
				target_owner = truppe[4]
				type = truppe[5]
				t_fe = truppe[6]
				t_h2o = truppe[7]
				t_uran = truppe[8]
				back = truppe[10]
				troops = truppe[11]
				atter_troops_dict = string_to_dict(troops,units_count)
				# bei Angriffen
				if type == 1:
					# Truppen des Verteidigers aus der Datenbank auslesen
					cursor.execute('SELECT fe,h2o,uran,time,troops,b1,b2,b3,b4,b5,b6,b7,b8,b9,b10,b11,b12,b13 FROM cities WHERE id=%s'%target)
					d_array = cursor.fetchall()[0]
					# Wenn der überhaupt Truppen hat
					if len(d_array) != 0:
						print "Deffer: "+str(d_array)
						deffer_exists = 1
						d_fe = d_array[0]
						d_h2o = d_array[1]
						d_uran = d_array[2]
						d_time = d_array[3]
						d_troops = d_array[4]
						d_buildings = d_array[5:17]
						print "CHECK Deffer Buildings: "+str(d_buildings)
						
						# geproddete Ressis des deffers aktualisieren
						fe_prod = d_buildings[2]*names_buildings[1][1]
						h2o_prod = d_buildings[3]*names_buildings[2][1]
						uran_prod = d_buildings[11]*names_buildings[11][1]
						time_diff = pointoftime - d_time
						
						d_fe += ((time_diff/3600.0)*fe_prod)
						d_h2o += ((time_diff/3600.0)*h2o_prod)
						d_uran += ((time_diff/3600.0)*uran_prod)
						
						# geproddete Truppen des deffers aktualisieren
						cursor.execute('SELECT * FROM events_production WHERE city_id=%s ORDER BY id'%target)
						prod_events = cursor.fetchall()
						active = 1
						for event in prod_events:
							if active != 0:
								e_id = event[0]
								e_unit_id = event[2]
								e_unit_count = event[3]
								e_starttime = event[4]
								if active != 1:
									cursor.execute('UPDATE events_production SET time=%s WHERE id=%s'%(active,e_id))
									e_starttime = active
								prodded_time = units[e_unit_id][2]
								prodded_units = 0
								active = 0
								time_gone = pointoftime - e_starttime
								while prodded_time < time_gone:
									prodded_units += 1
									prodded_time += units[e_unit_id][2]
								if prodded_units > 0:
									if prodded_units < e_unit_count:
									# Prodding-Event updaten
										update_time = e_starttime + prodded_units*units[e_unit_id][2]
										update_count = e_unit_count - prodded_units
										cursor.execute('UPDATE events_production SET time=%s, count=%s WHERE id=%s'%(update_time,update_count,e_id))
										deffer_troops_dict[e_unit_id] += prodded_units
									elif prodded_units >= e_unit_count:
										cursor.execute('DELETE FROM events_production WHERE id=%s'%e_id)
										deffer_troops_dict[e_unit_id] += e_unit_count
										active = e_starttime + prodded_time

						# Deffwert des deffers
						deff = 0
						deff_dict = string_to_dict('',units_count)
						for unit_id in deffer_troops_dict:
							unit_count = deffer_troops_dict[unit_id]
							if unit_count != 0:
								deff_dict[unit_id] = int(units[unit_id][1])*unit_count
								deff += int(units[unit_id][1])*unit_count
							else:
								deff_dict[unit_id] = 0
						# Offwert des atters
						off = 0
						off_dict = string_to_dict('',units_count)
						for unit_id in atter_troops_dict:
							unit_count = atter_troops_dict[unit_id]
							if unit_count != 0:
								off_dict[unit_id] = int(units[unit_id][0])*unit_count
								off += int(units[unit_id][0])*unit_count
							else:
								off_dict[unit_id] = 0
						print "Offwert: "+str(off)+"\n"+"Deffwert: "+str(deff)
						# Wenn der Atter gewinnt, verluste und geklaute ressis berechnen
						if off > deff:
							deffer_troops_new = string_to_dict('',units_count)
							verlust_multi =float(deff)/float(off)
							off_verlust = int(verlust_multi*off)
							verlust_dict = string_to_dict('',units_count)
							atter_troops_new = string_to_dict('',units_count)
							for unit_id in atter_troops_dict:
								unit_count = atter_troops_dict[unit_id]
								unit_multi = off_dict[unit_id]/float(off)
								verlust_dict[unit_id] = int(unit_multi*verlust_multi*unit_count)
								atter_troops_new[unit_id] = unit_count-verlust_dict[unit_id]
							atter_troops_space = 0
							for unit_id in atter_troops_new:
								unit_count = atter_troops_new[unit_id]
								units_space = int(units[unit_id][3])*unit_count
								atter_troops_space += units_space
							# vllt sowas wie Versteck/nicht klaubare Ressis einbauen ?
							# zuerst das Uran stibitzen
							if d_uran > atter_troops_space/3:
								a_uran = atter_troops_space/3
							else:
								a_uran = d_uran
							atter_troops_space -= a_uran
							d_uran = d_uran - a_uran
							# dann das Eisen
							if d_fe > atter_troops_space/2:
								a_fe = atter_troops_space/2
							else:
								a_fe = d_fe
							atter_troops_space -= a_fe
							d_fe = d_fe - a_fe
							# und zu guter letzt das Wasser
							if d_h2o > atter_troops_space:
								a_h2o = atter_troops_space
							else:
								a_h2o = d_h2o
							d_h2o = d_h2o - a_h2o
						else:
							a_fe,a_h2o,a_uran = 0,0,0
							
						# bei unentschieden gewinnt atter und 1 cyborg ueberlebt xD
						if off == deff:
							deffer_troops_new = string_to_dict('',units_count)
							atter_troops_new = string_to_dict('1;1',units_count)
				
						# wenn der Deffer gewinnt, verluste berechnen
						if off < deff:
							atter_troops_new = string_to_dict('',units_count)
							verlust_multi = float(off)/float(deff)
							deff_verlust = int(verlust_multi*deff)
							verlust_dict = string_to_dict('',units_count)
							deffer_troops_new = {}
							for unit_id in deffer_troops_dict:
								unit_count = deffer_troops_dict[unit_id]
								unit_multi = deff_dict[unit_id]/float(deff)
								verlust_dict[unit_id] = int(unit_multi*verlust_multi*unit_count)
								deffer_troops_new[unit_id] = unit_count-verlust_dict[unit_id]
								
						print "Deffer_new_dict: "+str(deffer_troops_new)
						print "Atter_new_dict: "+str(atter_troops_new)
				
						# neue Truppenstaerken in die DB eintragen
						d_troops = dict_to_string(deffer_troops_dict)
						atter_troops_final = dict_to_string(atter_troops_new)
						deffer_troops_final = dict_to_string(deffer_troops_new)
						
						print "Deffer_new_str: "+str(deffer_troops_final)
						print "Atter_new_str: "+str(atter_troops_final)
						
						cursor.execute("UPDATE cities SET troops='%s',fe='%s',h2o='%s',uran='%s',time='%s' WHERE id='%s'"%(deffer_troops_final,d_fe,d_h2o,d_uran,pointoftime,target))

						# Bericht posten
						cursor.execute("INSERT INTO reports (time,source,target,fe,h2o,uran,a_troops,d_troops,a_troops_final,d_troops_final) VALUES ('%s','%s','%s','%s','%s','%s','%s','%s','%s','%s')"%(pointoftime,source,target,a_fe,a_h2o,a_uran,troops,d_troops,atter_troops_final,deffer_troops_final))
						cursor.execute("SELECT id FROM reports WHERE time = '%s' AND source = '%s' AND target = '%s'"%(pointoftime,source,target))
						id = cursor.fetchall()[0][0]
						source_msg = '''Eine ihrer Truppen ist an ihrem Ziel angekommen. <a target="new" href="bericht.php?id='''+str(id)+'''">Zum Bericht</a>'''
						target_msg = '''Ihre Stadt wurde angegriffen. <a target="new" href="bericht.php?id='''+str(id)+'''">Zum Bericht</a>'''
						cursor.execute("INSERT INTO messages (owner,folder,sender_id,sender_city,`msg`,time,`read`) VALUES ('%s',5,'%s','%s','%s','%s',0)"%(source_owner,target_owner,target,source_msg,pointoftime))
						cursor.execute("INSERT INTO messages (owner,folder,sender_id,sender_city,`msg`,time,`read`) VALUES ('%s',5,'%s','%s','%s','%s',0)"%(target_owner,source_owner,source,target_msg,pointoftime))
						
					else:
						deffer_exists = 0
						d_fe,d_h2o,d_uran,d_time = 0,0,0,0
						a_fe,a_h2o,a_uran = 0,0,0
						d_buildings = string_to_dict('',buildings_count)
						d_troops = ''
						deffer_troops_dict = string_to_dict(d_troops,units_count)
						atter_troops_final = dict_to_string(atter_troops_dict)
						
					
					# Event updaten, falls Atter-Truppen ueberlebt haben
					if atter_troops_new != string_to_dict('',units_count):
						atter_troops_string = atter_troops_final
						cursor.execute("UPDATE events_troops SET troops='%s',type='2',fe='%s',h2o='%s',uran='%s',arrive='%s',back='0',target='%s',target_owner='%s',source='%s',source_owner='%s' WHERE id='%s'"%(atter_troops_string,a_fe,a_h2o,a_uran,back,source,source_owner,target,target_owner,id))
					else:
						cursor.execute("DELETE FROM events_troops WHERE id='%s'"%(id))
				
				## bei zurueckkehrenden Angriffstruppen
				elif type == 2:
					cursor.execute('SELECT troops,fe,h2o,uran FROM cities WHERE id=%s'%target)
					city_fetch = cursor.fetchall()
					if city_fetch != ():
						city_array = city_fetch[0]
						city_troops_dict = string_to_dict(city_array[0],units_count)
						new_fe = int(city_array[1])+t_fe
						new_h2o = int(city_array[2])+t_h2o
						new_uran = int(city_array[3])+t_uran
						ress_msg = "Sie hatte folgende Rohstoffe dabei:<br />Eisen: %s<br />Wasser: %s<br />Uran: %s"%(t_fe,t_h2o,t_uran)
						for unit_id in atter_troops_dict:
								unit_count = atter_troops_dict[unit_id]
								city_troops_dict[unit_id] += unit_count
						city_troops_new = dict_to_string(city_troops_dict)
						cursor.execute("UPDATE cities SET troops='%s',fe='%s',h2o='%s',uran='%s' WHERE id=%s"%(city_troops_new,new_fe,new_h2o,new_uran,target))
						cursor.execute("DELETE FROM events_troops WHERE id=%s"%id)
						cursor.execute("INSERT INTO messages (owner,folder,sender_id,sender_city,msg,time,`read`) VALUES ('%s','5','%s','%s','Eine ihrer Truppen ist zur&#252;ckgekehrt. %s','%s','0')"%(target_owner,source_owner,source,ress_msg,pointoftime))

		############################################################
		# Gebaeude Events bearbeiten ###############################
		############################################################
		cursor.execute("SELECT * FROM events_buildings WHERE time<='%s'"%pointoftime)
		build_events = cursor.fetchall()
		if len(build_events) != 0:
			for event in build_events:
				id = event[0]
				owner = event[1]
				city = event[2]
				building = event[3]
				db_field = 'b'+str(building)

				cursor.execute("SELECT "+db_field+" FROM cities WHERE id='%s'"%city)
				level = cursor.fetchall()[0][0]
				print "LEVEL: "+str(level)
				level += 1
				print "LEVEL: "+str(level)
				name = names_buildings[building][0]

				cursor.execute("UPDATE cities SET "+db_field+"='%s' WHERE id='%s'"%(level,city))
				cursor.execute("DELETE FROM events_buildings WHERE id='%s'"%id)
				
				cursor.execute("INSERT INTO messages (owner,folder,sender_id,sender_city,msg,time,`read`) VALUES ('%s','3','%s','%s','Bau von %s (Stufe %s) ist abgeschlossen','%s','0')"%(owner,owner,city,name,level,pointoftime))

		############################################################
		# Forschungs Events bearbeiten #############################
		############################################################
		cursor.execute("SELECT * FROM events_research WHERE time<='%s'"%pointoftime)
		research_events = cursor.fetchall()
		if len(research_events) != 0:
			for event in research_events:
				id = event[0]
				owner = event[1]
				research = event[2]
				db_field = 'r'+str(research)

				cursor.execute("SELECT "+db_field+" FROM gamer_research WHERE id='%s'"%owner)
				level_array = cursor.fetchall()
				if len(level_array) != 0:
					level = level_array[0][0] + 1
				else:
					level = 1
				name = names_research[research][0]
				
				gamer_research_new = dict_to_string(gamer_research_dict)
				cursor.execute("UPDATE gamer_research SET "+db_field+"='%s' WHERE id='%s'"%(level,owner))
				cursor.execute("DELETE FROM events_research WHERE id='%s'"%id)
				
				cursor.execute("INSERT INTO messages (owner,folder,sender_id,msg,time,`read`) VALUES ('%s','4','%s','Forschung von %s (Stufe %s) ist abgeschlossen','%s','0')"%(owner,owner,name,level,pointoftime))
	else:
		sleep(1)
	# Fuer debugging zwecke = 0, sonst logischerweise = 1
	unendlich = 1
	nexttime = pointoftime+1
