��    .      �  =   �      �     �  1        3     F     `     r  �   �     z          �     �     �  	   �     �     �  M   �  I        U  
   [     f  	   x  
   �     �     �  M   �  �     V   �     H     K  E   R     �     �  �   �     +	  
   4	  _   ?	  �   �	  �   +
  	   �
  o   �
  "   /     R     Z     b     f  �  �  '   9  A   a  %   �  $   �  %   �         4     :     ?     S     i     p       
   �     �  2   �  a   �     @     I     e     �     �  #   �  '   �  ?   �  >   ,  �   k     �       _   
     j  
   z  �   �  
   c     n  �   }  �   
  �   �        r   4  #   �     �  
   �     �     �     $       +                    !                                       "          -                     
          *            )   #       (      %                     ,   &   '               	      .              Append Hostname Appends the hostname to the name of the log files Asterisk Log Files Asterisk Logfile Settings Asterisk Logfiles Critical errors and issues Customize the display of debug message time stamps. See strftime(3) Linux manual for format specifiers. Note that there is also a fractional second parameter which may be used in this field.  Use %1q for tenths, %2q for hundredths, etc. DTMF Date Format Debug Error Fax File Name Filter General Settings Keypresses as understood by asterisk. Usefull for debuging IVR and VM issues. Leave blank for default: ISO 8601 date format yyyy-mm-dd HH:MM:SS (%F %T) Lines Loading... Log File Settings Log Files Log Queues Log Rotation Log queue events to a file Messages of specific actions, such as a phone registration or call completion Messages used for debuging. Do not report these as error's unless you have a specific issue that you are attempting to debug. Also note that Debug messages are also very verbose and can and do fill up logfiles (and disk storage) quickly. Name of file, relative to Asterisk logpath. Use absolute path for a different location No Notice Possible issues with dialplan syntaxt or call flow, but not critical. Reports Rotate Rotate: Rotate all the old files, such that the oldest has the highest sequence number (expected behavior for Unix administrators). Security Sequential Sequential: Rename archived logs in order, such that the newest has the highest sequence number Setting this to yes will interfere with log rotation and Intrusion Detection.  It is strongly recommended that this setting be set to 'no'. Setting this to yes will interfere with log rotation and Intrusion Detection. It is strongly recommended that this setting be set to: no. Timestamp Timestamp: Rename the logfiles using a timestamp instead of a sequence number when "logger rotate" is executed. Transmition and receiving of faxes Verbose Warning Yes appendhostname is set to: Yes. Project-Id-Version: PACKAGE VERSION
Report-Msgid-Bugs-To: 
POT-Creation-Date: 2020-07-23 14:29+0530
PO-Revision-Date: 2019-08-13 12:03+0000
Last-Translator: Media Mousavi <mousavi.media@gmail.com>
Language-Team: Persian <http://*/projects/freepbx/logfiles/fa/>
Language: fa_IR
MIME-Version: 1.0
Content-Type: text/plain; charset=UTF-8
Content-Transfer-Encoding: 8bit
Plural-Forms: nplurals=2; plural=n != 1;
X-Generator: Weblate 3.0.1
 اضافه کردن نام میزبان اضافه کردن نام میزبان به لاگ فایل ها لاگ فایل های استریسک تنظیمات لاگ استریسک لاگ فایل های استریسک خطاها و موارد مهم شخصی سازی نمایش برچسب زمانی پیام عیب یابی. See strftime(3) Linux manual for format specifiers. Note that there is also a fractional second parameter which may be used in this field.  Use %1q for tenths, %2q for hundredths, etc. DTMF فرمت تاریخ اشکال زدایی خطا دورنگار نام فیلتر تنظیمات عمومی مفید برای عیب یابی منو صوتی. برای پیشفرض خالی بگذارید ：ISO 8601 date format yyyy-mm-dd HH:MM:SS (%F %T) خطوط درحال لود شدن... تنظیمات لاگ فایل لاگ فایل صف لاگ ورود به سیستم چرخشی لاگ صف رویدادهای فایل پیام عملکردهای مشخص, مانندثبت تلفن پیامهای استفاده شده برای عیب یابی. نام فیلتر, مرتبط با الگو لاگ استریسک. استفاده از الگو مشخص برای مکان های متفاوت خیر توجه مشکلات احتمالی syntaxt dialplan یا جریان پاسخ، اما مهم نیست. گزارش ها چرخشی چرخش: چرخش تمام فایل های قدیمی، به طوری که از قدیمی ترین دارای بیشترین تعداد دنباله (رفتار مورد انتظار برای مدیران یونیکس). امنیت پی در پی ترتیبی: تغییر نام آرشیو لاگ، به طوری که، جدید ترین دارای بیشترین تعداد دنباله Setting this to yes will interfere with log rotation and Intrusion Detection. It is strongly recommended that this setting be set to 'no'. Setting this to yes will interfere with log rotation and Intrusion Detection. It is strongly recommended that this setting be set to : no. برچسب زمان Timestamp: Rename the logfiles using a timestamp instead of a sequence  number when "logger rotate" is  executed . انتقال و دریافت فکس بلند هشدار بله appendhostname is set to : Yes. 