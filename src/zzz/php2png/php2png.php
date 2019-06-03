<?php
  if (count($argv) != 3) {
    echo "Usage: php -f src/zzz/php2png/php2png.php <php_file> <png_file>\n";
    die();
  }

  $src_filename = $argv[1];
  $dst_filename = $argv[2];

  if (file_exists($src_filename) && is_file($src_filename)) {
    $src = file_get_contents($src_filename);
  } else {
    echo "Can't read the $src_filename\n";
  }

  $buffer = array();
  $payload_sz = strlen($src);

  $buffer[] = 0;
  $buffer[] = ($payload_sz >> 56 & 0xff);
  $buffer[] = ($payload_sz >> 48 & 0xff);
  $buffer[] = 255;
  $buffer[] = ($payload_sz >> 40 & 0xff);
  $buffer[] = ($payload_sz >> 32 & 0xff);
  $buffer[] = ($payload_sz >> 24 & 0xff);
  $buffer[] = 255;
  $buffer[] = ($payload_sz >> 16 & 0xff);
  $buffer[] = ($payload_sz >> 8 & 0xff);
  $buffer[] = ($payload_sz & 0xff);
  $buffer[] = 255;

  $data_sz = ceil($payload_sz / 3.0);
  
  for ($i = 0; $i < $data_sz; $i++) {
    $p = $i * 3;
    $buffer[] = ord(substr($src, $p, 1));
    $buffer[] = ord(substr($src, $p + 1, 1));
    $buffer[] = ord(substr($src, $p + 2, 1));
    $buffer[] = 255;
  }

  $bitmap_sz = round(count($buffer) / 4);
  $w = ceil(sqrt($bitmap_sz));
  $h = ceil($bitmap_sz / $w);

  $im = imagecreate($w, $h);

  $color = imagecolorallocatealpha($im, 0, 0, 0, 127);
  imagefill($im, 0, 0, $color);

  $index = 0;
  for ($y = 0; $y < $h; $y++) {
    for ($x = 0; $x < $w; $x++) {
      if (($y * $w + $x) < count($buffer) / 4) {
        $color = imagecolorallocate($im, $buffer[$index], $buffer[$index + 1], $buffer[$index + 2]);
        imagesetpixel($im, $x, $y, $color);
        $index += 4;
      }
    }
  }

  header("Content-Type: image/png");
  imagepng($im, $dst_filename);
  imagedestroy($im);
?>