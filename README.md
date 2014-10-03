simplebb v1.1 for DLE ( DataLife Engine )
========
![simplebb logo][logo]

* Yapımcı: [Mehmet Hanoğlu]
* Dizayn : [Kadir Hanoğlu]
* Site   : http://dle.net.tr
* Tarih  : 03.10.2014
* Lisans : MIT License
* DLE    : 10.3, 10.2
* Translations: English ( [DLEStarter] ), Russian ( [mrB4el] ), Ukrainian ( [Corsair] )


Genel Açıklamalar :
========

Hiyerarşi
--------------
Forum kategori ve alt kategori olmak üzere 2 derinliğe göre ayarlanmıştır. 1. Derinlik için kategori, 2. Derinlik için forum diyoruz.

Hiyerarşi olarak bu şekilde : SimpleBB > Kategori > Forum > Konular

Temanın şablon dosyasında ise aynı sıra ile [depth=1], [depth=2], [depth=3], [depth=4] tanımlanmıştır.

DLE kategori sistemini kullandığı için her türlü düzenlemeyi yapabilirsiniz.

Sistem İşleyişi
--------------------
Ana kategorilerden forum için bir adet seçiyoruz. Bu kategorinin alt kategorileri için özel .tpl ler tanımlıyoruz.

Sadece forum anasayfası için tema klasörünüzdeki /forum/*.tpl dosyaları kullanılıyor.

Forum için belirlediğiniz kategoriye eriştiğinizde konular yerine sistem tarafından derlenen forumlar listelenecektir.


Ayarlar :
========

Subdomain Kullanımı
--------------------
SimpleBB subdomain kullanımı destekliyor. Bununla beraber gerekli yönlendirme ve SEO önlemleri alındı.

Subdomain kullanımı açıldığında subdomain siz olarak girilen tüm forum linkleri otomatik olarak subdomain li haline yönlendirilecek.

Yönlendirme vs. oluşabilecek hatalar nedeniyle canonical linki tanımlandı. Böylece forumdaki her konuda canonical metası mevcut olacaktır.

###Yapmanız gereken ek işlemler :

.htaccess dosyasına eklemeniz gereken kod ( DLE.NET.TR için örnek kullanım. "forum" subdomaini için yazılmıştır. )
~~~
# Forum redirect
RewriteCond %{HTTP_HOST} ^forum\.dle\.net\.tr$ [NC]
RewriteRule ^$ index.php?do=cat&category=forum [L,NC]
~~~

DLETR.COM için örnek yazım.
~~~
RewriteCond %{HTTP_HOST} ^forum\.dletr\.com$ [NC]
~~~

Bu eklemeden sonra CPanel'den ile dinamik subdomain oluşturmalısınız.

Subdomain alanına sadece * ( Yıldız ) yazarak "Kaydet" e basın. Bu şekilde herhangi bir subdomain yazımını .htaccess ile istediğiniz linke yönlendirebilirsiniz.
Fakat arama motorlarının indexlerinde problem çıkarması ihtimaline karşı. Sadece kullanacağınız subdomain'i ekleyin. Yani * yerine forum (subdomain adı) yazınız.

SQL Optimizasyonu
--------------------
Daha önce yapılan sisteme göre sorgu sayısı çok düşürüldü. Toplam 2-3 sorgu ile anasayfa hazır hale geliyor. Global değişkenler yardımıyla kategoriler hakkında bilgiler toplanıyor.


Kurulum ve Kaldırma :
===========

Kurulum
--------------
1) Kuruluma başlamadan sitenizi yedekelemenizi tavsiye ederiz.

2) Forum için kullanacağınız kategorileri sırayla oluşturunuz. ( [Ekran Görüntüsü: Kategoriler] )

3) Her forum için bilgileri ve belirlenen özel şablon dosyalarını giriniz. Alttaki şablon belirleme kısmını aynen yapınız. ( [Ekran Görüntüsü: Forum Bilgileri] )

4) Forumun tüm kategorileri ve ayarlamaları tamam ise. Tüm dosyaları sitenize yükleyerek kuruluma geçebilirsiniz.

5) Kurulum için install_module.php dosyasını çalıştırın. Kurulum xml sistemi ile otomatik olarak yapılıyor. Yani tüm dosyalarınız sistem tarafından düzenleniyor ve dosyaların orjinalleri arşivlenerek install/backup/ dizinine kaydediliyor.

6) Kurulumdan hemen sonra admin paneline girerek "Sistem Ayarları" na geçiniz. ( [Ekran Görüntüsü: Ayarlar] ) 

7) Eğer subdomain kullanacaksanız, ayarı aktifleştirdikten sonra yukarıdaki açıklamalarda bulunan "Subdomain Kullanımı" başlığını okuyup uygulayınız.

8) Sitenizdeki herhangi bir kategoriye eriştiğiniz gibi /forum olarak erişebilirsiniz. ( Forum için hangi url'yi belirediyseniz. [Ekran Görüntüsü: Forum Kategorisi] )

9) Temanızda main.tpl dosyasını açarak </head> tagının üstüne aşağıdaki kodu ekleyin

~~~
[forum]<link media="screen" href="{THEME}/forum/css/simplebb.css" type="text/css" rel="stylesheet" />[/forum]
~~~


Güncelleme ( v1.0 => v1.1 )
--------------
* Modülü tamamen kaldırarak yükleme yapmanız en problemsiz seçim olacaktır. Fakat eğer uygun değilse aşağıdaki adımı uygulayabilirsiniz.
* Tüm dosyaları kopyalayarak, sıradan bir kurulum gerçekleştiriniz.


Kaldırma
--------------
* Sistemi kaldırmak istediğinizde install/backup dizindeki yedek dosyasını açarak sitenize upload etmeniz yeterli.


Şablon ve Etiketler :
===========
Forum toplam 4 adet .tpl dosyası ile çalışıyor.
main.tpl, stats.tpl ve her forum için seçilen post.tpl, threads.tpl dosyaları.

Default tema [Kadir Hanoğlu] tarafından tasarlanmıştır. [Ekran Görüntüsü: Default Tema]

Herhangi bir .tpl dosyasında kullanılabilir taglar
--------------
~~~
[forum:main]Forum ana sayfası[/forum:main]
[forum:cat]Kategori sayfası[/forum:cat]
[forum:forum]Forum sayfası[/forum:forum]
[forum:inside]Kategori ya da forum sayfası[/forum:inside]
[forum:thread]Forumdaki bir konu sayfası[/forum:thread]
[forum]Forum ile ilgili her hangi bir sayfa[/forum]
~~~

~~~
{forum-stats}          : Forum istatistikleri ( forum/stats.tpl şablonundan değiştirilebilir )
{category echo="id"}   : Mevcut kategori ID'si ( show.short sayfasında çalışmaz )
{category echo="name"} : Mevcut kategori adı ( show.short sayfasında çalışmaz )
{page-title}           : show.full içinde sayfa başlığı ( {title} ) yerine kullanılabilir.
{count_all}            : Kategori sayfalarında, o kategoriye ait kaç adet makalenin bulunduğunu gösterir
{user-group}           : show.full içinde kullanıcı grubunu temsil eder. Panelden belirlenen stil ile gösterir.
{avatar}               : show.full içinde kullanıcının avatar urlsini çeker.
~~~

Kullanılan eklentiler: [Eklenti #163], [Eklenti #45], [Eklenti #41] 


addpost.tpl de kullanılabilir taglar
--------------
addpost.tpl dosyası addnews.tpl dosyanızın aynısı olabilir, sadece kategori seçimini kaldırmanız gerekli.

Bu şablon dosyası için kullanılabilir tag, {selected-cat} belirtilen kategorinin adıdır:
~~~
{selected-cat}
~~~

Modül Hakkında
======================

Bilgiler
-----------------
Detaylı bilgi : http://forum.dle.net.tr/gelistiriciler/fikirler-ve-projeler/39-simplebb-forum.html

Modül sayfası : http://dle.net.tr/dle-modul/155-dle-icin-ucretsiz-form-simplebb.html

English information : http://www.dlestarter.com/downloads/modules/432-simple-bb-forum-for-dle.html

Yenilikler
-----------------------
* 10.2 ve 10.3 için uyumlu hale getirildi.
* Rusça, Ukraynaca ve İngilizce çevirileri dahil edildi.
* Forumdaki son mesaj bilgileri için kullanılan hatalı SQL düzeltildi.
* Subdomain kullanımı açıksa, makaleyi subdomainli adresine yönlendirme eklendi.
* Forum açıklaması için kullanılan değişken 'descr' olarak ayarlandı. Yani {desc} tagı ile artık açıklamalar çekilecek.
* Kurulumdan sonraki ayarlar için onarmalar ve ayarlamalar yapıldı.
* Temada kullanılan font-awesome ikon kütüphanes 4.2.0 versiyonuna güncellendi.


Tarihçe
-----------------------
* 03.10.2014 (v1.1)
* 23.07.2014 (v1.0)
* 15.07.2014 (v1.0-beta)


[Kadir Hanoğlu]:https://github.com/kadirhanoglu
[Mehmet Hanoğlu]:https://github.com/marzochi
[DLEStarter]:http://dlestarter.com
[mrB4el]:http://www.minezone.pro/
[Corsair]:http://webexpert.in.ua/
[Ekran Görüntüsü: Ayarlar]:http://dle.net.tr/uploads/posts/1406114470_settings.png
[Ekran Görüntüsü: Forum Bilgileri]:http://dle.net.tr/uploads/posts/1406113700_forum.png
[Ekran Görüntüsü: Kategoriler]:http://dle.net.tr/uploads/posts/1406113626_cats.png
[Ekran Görüntüsü: Forum Kategorisi]:http://dle.net.tr/uploads/posts/1406114739_forumcat.png
[Eklenti #163]:http://dle.net.tr/dle-eklenti/163-kategori-bilgisi-cekme.html
[Eklenti #45]:http://dle.net.tr/dle-eklenti/45-sayfa-basligini-tag-olarak-kullanma.html
[Eklenti #41]:http://dle.net.tr/dle-eklenti/41-makaleyi-ekleyenin-avatarini-gosterme.html
[Ekran Görüntüsü: Default Tema]:http://blog.dle.net.tr/blog/181-simplebb-default-theme-yayinda.html
[logo]:http://dle.net.tr/uploads/posts/1406126090_simplebb-default-theme-logo.png

