��    .      �  =   �      �     �  1        3     F     `     r  �   �     z          �     �     �  	   �     �     �  M   �  I        U  
   [     f  	   x  
   �     �     �  M   �  �     V   �     H     K  E   R     �     �  �   �     +	  
   4	  _   ?	  �   �	  �   +
  	   �
  o   �
  "   /     R     Z     b     f  �  �     ?  9   S     �  .   �     �     �  E       T     X     e     k     r  	   v     �     �  p   �  L        ^     e     m     �     �     �  6   �  Y   �  #  Y  t   }     �     �  P   �     P     Y  �   b  
   �            �   �  �   <     �  �   �     u     �     �     �  !   �     $       +                    !                                       "          -                     
          *            )   #       (      %                     ,   &   '               	      .              Append Hostname Appends the hostname to the name of the log files Asterisk Log Files Asterisk Logfile Settings Asterisk Logfiles Critical errors and issues Customize the display of debug message time stamps. See strftime(3) Linux manual for format specifiers. Note that there is also a fractional second parameter which may be used in this field.  Use %1q for tenths, %2q for hundredths, etc. DTMF Date Format Debug Error Fax File Name Filter General Settings Keypresses as understood by asterisk. Usefull for debuging IVR and VM issues. Leave blank for default: ISO 8601 date format yyyy-mm-dd HH:MM:SS (%F %T) Lines Loading... Log File Settings Log Files Log Queues Log Rotation Log queue events to a file Messages of specific actions, such as a phone registration or call completion Messages used for debuging. Do not report these as error's unless you have a specific issue that you are attempting to debug. Also note that Debug messages are also very verbose and can and do fill up logfiles (and disk storage) quickly. Name of file, relative to Asterisk logpath. Use absolute path for a different location No Notice Possible issues with dialplan syntaxt or call flow, but not critical. Reports Rotate Rotate: Rotate all the old files, such that the oldest has the highest sequence number (expected behavior for Unix administrators). Security Sequential Sequential: Rename archived logs in order, such that the newest has the highest sequence number Setting this to yes will interfere with log rotation and Intrusion Detection.  It is strongly recommended that this setting be set to 'no'. Setting this to yes will interfere with log rotation and Intrusion Detection. It is strongly recommended that this setting be set to: no. Timestamp Timestamp: Rename the logfiles using a timestamp instead of a sequence number when "logger rotate" is executed. Transmition and receiving of faxes Verbose Warning Yes appendhostname is set to: Yes. Project-Id-Version: PACKAGE VERSION
Report-Msgid-Bugs-To: 
POT-Creation-Date: 2020-07-23 14:29+0530
PO-Revision-Date: 2019-06-25 15:58+0000
Last-Translator: Bastian Mertgen <b.mertgen@bastian-mertgen.de>
Language-Team: German <http://*/projects/freepbx/logfiles/de/>
Language: de_DE
MIME-Version: 1.0
Content-Type: text/plain; charset=UTF-8
Content-Transfer-Encoding: 8bit
Plural-Forms: nplurals=2; plural=n != 1;
X-Generator: Weblate 3.0.1
 Hostnamen anhängen Hängt den Hostnamen an den Namen der Protokolldateien an Asterisk-Protokolldateien Einstellungen für die Asterisk-Protokolldatei Asterisk-Protokolldateien Kritische Fehler und Probleme Passen Sie die Darstellung der Zeitstempel in den Debug-Nachrichten an. die Manpage zu strftime(3) gibt Auskunft über die Formatierungsangaben. Beachten Sie, dass es einen zweiten Parameter für Bruchteile von Sekunden gibt, der in diesem Feld verwendet werden kann. Verwenden Sie %1q für Zehntel, %2q für Hundertstel etc. MFV Datumsformat Debug Fehler Fax Dateiname Filter Allgemeine Einstellungen Tastendrücke, wie sie von Asterisk verstanden werden. Nützlich für die Fehlersuche bei IVR- und VM-Problemen. Leer lassen für Standardformat (nach ISO 8601): yyyy-mm-dd HH:MM:SS (%F %T) Zeilen Lade... Protokolldateieinstellungen Protokolldateien Warteschlangen überwachen Protokoll-Rotation Warteschlangenereignisse in einer Datei protokollieren Nachrichten zu bestimmten Aktionen wie z.B. Telefonregistrierungen oder getätigte Anrufe Nachrichten zur Fehlerbereinigung. Melden Sie diese nicht als Fehler, sofern Sie kein spezifisches Problem haben, das Sie korrigieren wollen. Bedenken Sie auch, dass die Meldungen zur Fehlerbereinigung sehr ausführlich sind und Protokolldateien (und Plattenplatz) schnell volllaufen lassen. Dateiname, relativ zum Asterisk-Protokollpfad. Geben sie einen absoluten Pfad für einen abweichenden Speicherort an Nein Hinweis Mögliche aber unkritische Probleme mit der Wählplansyntax oder dem Anruffluss. Berichte Rotieren Rotieren: Rotiere alle alten Dateien so, dass die älteste jeweils die höchste fortlaufende Nummer hat (erwartetes Verhalten für Unix-Administratoren). Sicherheit Aufeinanderfolgend Aufeinanderfolgend: Benenne die archivierten Protokolle der Reihe nach, so dass das neuste die höchste fortlaufende Nummer hat Wird dies auf 'ja' gestellt, beeinflusst dies die Log-Rotation und die Eindringungserkennung. Es wird dringend empfohlen, die Einstellung auf 'nein' zu belassen. Wird dies auf 'ja' gestellt, beeinflusst dies die Log-Rotation und die Eindringungserkennung. Es wird dringend empfohlen, die Einstellung auf 'nein' zu belassen. Zeitstempel Zeitstempel: Benenne die Protokolle mit einem Zeitstempel statt mit einer fortlaufenden Nummer, wenn „logger rotate“ ausgeführt wird. Versand und Empfang von Faxen Ausführlich Warnung Ja 'appendhostname' steht auf: 'ja'. 