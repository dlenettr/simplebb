simplebb for DLE ( DataLife Engine )
========
* Yapımcı: Mehmet Hanoğlu ( dle.net.tr )
* Tarih  : 15.07.2014
* Lisans : MIT
* DLE    : Yalnızca 10.2


Açıklamalar :
--------------------
Forum kategori ve alt kategori olmak üzere 2 derinliğe göre ayarlanmıştır. 1. Derinlik için kategori, 2. Derinlik için forum diyoruz.
Hiyerarşi olarak bu şekilde : SimpleBB > Kategori > Forum > Konular
DLE kategori sistemini kullandığı için her türlü düzenlemeyi yapabilirsiniz.

Sistem İşleyişi
--------------------
Ana kategorilerden forum için bir adet seçiyoruz. Bu kategorinin alt kategorileri için özel .tpl ler tanımlıyoruz.
Sadece forum anasayfası için tema klasörünüzdeki /forum/*.tpl dosyaları kullanılıyor.
Forum için belirlediğiniz kategoriye eriştiğinizde konular yerine sistem tarafından derlenen forumlar listelenecektir. Yani sadece anasayfa için düzenleme yapmış oluyoruz.

Subdomain Kullanımı
--------------------
SimpleBB subdomain kullanımı destekliyor. Bununla beraber gerekli yönlendirme ve SEO önlemleri alındı.
Subdomain kullanımı açıldığında subdomain siz olarak girilen tüm forum linkleri otomatik olarak subdomain li haline yönlendirilecek.
Yönlendirme vs. oluşabilecek hatalar nedeniyle canonical linki tanımlandı. Böylece forumdaki her konuda canonical metası mevcut olacaktır.


SQL Optimizasyonu
--------------------
Daha önce yapılan sisteme göre sorgu sayısı çok düşürüldü. Toplam 2-3 sorgu ile anasayfa hazır hale geliyor. Global değişkenler yardımıyla kategoriler hakkında bilgiler toplanıyor.


Kurulum ve Kaldırma
--------------------
* Kurulum xml sistemi ile otomatik olarak yapılıyor. Yani tüm dosyalarınız sistem tarafından düzenleniyor ve dosyaların orjinalleri arşivlenerek install/backup/ dizinine kaydediliyor.
* Sistemi kaldırmak istediğinizde bu yedek dosyasını açarak sitenize upload etmeniz yeterli.


Şablon Düzenlemeleri :
--------------------

Herhangi bir .tpl dosyasında kullanılabilir taglar
--------------------

~~~
[forum:main]Forum Ana Sayfası[/forum:main]
[forum:cat]Kategori Sayfası[/forum:cat]
[forum:forum]Forum Sayfası[/forum:forum]
[forum:inside]Kategori ya da Forum Sayfası[/forum:inside]
~~~

~~~
{forum-stats}   : Forum istatistikleri ( forum/stats.tpl şablonundan değiştirilebilir )
{category-id}   : Mevcut kategori ID'si
{category-name} : Mevcut kategori adı
~~~

Detaylı bilgi ve fikir bildirimleriniz için : http://forum.dle.net.tr/gelistiriciler/fikirler-ve-projeler/39-simplebb-forum.html
