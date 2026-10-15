# Codex AI Blog

WordPress 6.4+ / PHP 7.4+ için Perplexity ve OpenAI destekli blog taslağı üreticisi.

## Kurulum

1. Bu klasörü `wp-content/plugins/codex-ai-blog` dizinine kopyalayın veya dağıtım ZIP dosyasını **Eklentiler → Yeni eklenti ekle → Eklenti yükle** üzerinden yükleyin.
2. Eklentiyi etkinleştirin. **Araçlar → Codex AI Blog** ekranını açın.
3. Perplexity API anahtarınızı ve görsel kullanacaksanız OpenAI API anahtarınızı kaydedin. Alternatif olarak `wp-config.php` dosyasında `CODEX_BLOG_PERPLEXITY_KEY` ve `CODEX_BLOG_OPENAI_KEY` sabitlerini tanımlayın. Gerçek anahtarları Git'e eklemeyin.
4. Anahtar kelimeleri ve içerik açıklamasını girerek taslak oluşturun. İçerik, kaynaklar, meta açıklaması ve görselleri inceleyip WordPress editöründen yayımlayın.

## Davranış

- Perplexity `sonar-pro` ile Türkçe makale ve kaynak bağlantıları üretir.
- Görünür makale metni 1600 karakterin altındaysa taslak oluşturmaz.
- OpenAI `gpt-image-1` ile bir öne çıkan görsel ve bir içerik görseli üretir; medya kütüphanesine alternatif metinle kaydeder.
- Görsel hatasında metin taslağını ve başarılı medya dosyalarını korur; hata mesajında taslak numarasını gösterir.
- Yoast SEO ve Rank Math başlık/açıklama/odak kelime alanlarını doldurur. Bu eklentiler yoksa meta description ve BlogPosting JSON-LD çıktısı verir. WordPress yazı başlığını sayfa başlığı için kullanır; temanın standart title-tag desteği gerekir.
- Üretim yalnız yetkili yöneticinin isteğiyle başlar; zamanlanmış otomatik yayın bu sürümde yoktur.
- API anahtarları arayüze geri basılmaz. Veritabanında saklanan anahtarlar WordPress seçenekleri içinde şifrelenmeden tutulur; üretimde wp-config sabitlerini ve güvenli yedekleme kullanın.

## Gereksinimler ve sınırlar

Sunucunun `api.perplexity.ai` ve `api.openai.com` için HTTPS erişimi, yazılabilir medya dizini ve görsel işleme desteği gerekir. Her iki serviste yeterli bakiye/model erişimi bulunmalıdır. OpenAI görsel modeli hesap doğrulaması isteyebilir. İki görselli istek için PHP/web sunucusu zaman aşımını uygun tutun (örn. 420 saniye); her API isteğinin üst sınırı 120 saniyedir. Kesilen işlem tekrar gönderilirse ek ücret oluşabilir. Üretim kilidi 10 dakika sonra eski kabul edilir.

SEO/GEO yapısı görünürlüğe yardımcı olur; Google veya yapay zekâ aramalarında görünme/sıralama garantisi değildir. Kaynakların iddiaları gerçekten desteklediğini, doğruluğu ve görsel uygunluğunu yayımlamadan önce kontrol edin. API'lere konu ve açıklama gönderilir; kişisel veya gizli veri girmeyin. Perplexity/OpenAI ücretleri WordPress'ten bağımsızdır.

## Doğrulama

`php -l codex-blog.php` ile sözdizimini denetleyin. Gerçek WordPress ortamında yetki/nonce kontrolünü, anahtar kaydetme/silme işlemlerini, görselsiz ve görselli taslağı, minimum uzunluğu, medya alternatif metinlerini, API hata durumunda taslak korunmasını ve yayımlanmış sayfanın meta/JSON-LD çıktısını kontrol edin. Canlı üretim testleri geçerli API anahtarları ve ücretli API çağrıları gerektirir.
