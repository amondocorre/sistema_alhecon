<?php
defined('BASEPATH') OR exit('No direct script access allowed');
 Class Nocache{
  function set_no_cache_headers() {
    header("Cache-Control: no-cache, no-store, must-revalidate");
    header("Pragma: no-cache");
    header("Expires: 0");
  }
}
