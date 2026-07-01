{ pkgs }: {
  deps = [
    pkgs.php
    pkgs.php81Extensions.pdo_sqlite
    pkgs.php81Extensions.curl
    pkgs.php81Extensions.json
    pkgs.php81Extensions.mbstring
  ];
  env = {
    PHP_EXTENSION_DIR = "${pkgs.php81Extensions.pdo_sqlite}/lib/php/extensions";
  };
}
