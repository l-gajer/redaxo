<?php

class rex_install_webservice
{
  const
    HOST = 'www.redaxo.org',
    PORT = 443,
    PREFIX = 'ssl://',
    PATH = '/de/ws/',
    REFRESH_CACHE = 600;

  static private $cache;

  static public function getJson($path)
  {
    if(is_array($cache = self::getCache($path)))
    {
      return $cache;
    }
    $path = self::getPath($path);
    $fullpath = self::PATH . $path;

    $error = null;
    try
    {
      $socket = new rex_socket(self::HOST, $fullpath, self::PORT, self::PREFIX);
      $socket->doGet();
      if($socket->getStatus() == 200)
      {
        $data = json_decode($socket->getBody(), true);
        if(isset($data['error']) && is_string($data['error']))
        {
          $error = rex_i18n::msg('install_webservice_error') .'<br />'. $data['error'];
        }
        elseif(is_array($data))
        {
          self::setCache($path, $data);
          return $data;
        }
      }
    }
    catch(rex_socket_exception $e)
    {
      rex_logger::logException($e);
    }

    if(!$error)
      $error = rex_i18n::msg('install_webservice_unreachable');

    throw new rex_functional_exception($error);
  }

  static public function getArchive($url)
  {
    try
    {
      $socket = rex_socket::createByUrl($url);
      $socket->doGet();
      if($socket->getStatus() == 200)
      {
        $filename = basename($url);
        $file = rex_path::cache('install/'. md5($filename) .'.'. rex_file::extension($filename));
        $socket->writeBodyTo($file);
        return $file;
      }
    }
    catch(rex_socket_exception $e)
    {
      rex_logger::logException($e);
    }

    throw new rex_functional_exception(rex_i18n::msg('install_archive_unreachable'));
  }

  static public function post($path, array $data, $archive = null)
  {
    $fullpath = self::PATH . self::getPath($path);
    $error = null;
    try
    {
      $socket = new rex_socket(self::HOST, $fullpath, self::PORT, self::PREFIX);
      $files = array();
      if($archive)
      {
        $files['archive']['path'] = $archive;
        $files['archive']['type'] = 'application/zip';
      }
      $socket->doPost($data, $files);
      if($socket->getStatus() == 200)
      {
        $data = json_decode($socket->getBody(), true);
        if(!isset($data['error']) || !is_string($data['error']))
          return;
        $error = rex_i18n::msg('install_webservice_error') .'<br />'. $data['error'];
      }
    }
    catch(rex_socket_exception $e)
    {
      rex_logger::logException($e);
    }

    if(!$error)
      $error = rex_i18n::msg('install_webservice_unreachable');

    throw new rex_functional_exception($error);
  }

  static public function delete($path)
  {
    $fullpath = self::PATH . self::getPath($path);
    $error = null;
    try
    {
      $socket = new rex_socket(self::HOST, $fullpath, self::PORT, self::PREFIX);
      $socket->doDelete();
      if($socket->getStatus() == 200)
      {
        $data = json_decode($socket->getBody(), true);
        if(!isset($data['error']) || !is_string($data['error']))
          return;
        $error = rex_i18n::msg('install_webservice_error') .'<br />'. $data['error'];
      }
    }
    catch(rex_socket_exception $e)
    {
      rex_logger::logException($e);
    }

    if(!$error)
      $error = rex_i18n::msg('install_webservice_unreachable');

    throw new rex_functional_exception($error);
  }

  static private function getPath($path)
  {
    $path = strpos($path, '?') === false ? rtrim($path, '/') .'/?' : $path .'&';
    $path .= 'rex_version='. rex::getVersion();
    $addon = rex_addon::get('install');
    if($addon->getConfig('api_login'))
    {
      $path .= '&api_login='. $addon->getConfig('api_login') .'&api_key='. $addon->getConfig('api_key');
    }
    return $path;
  }

  static public function deleteCache($pathBegin = null)
  {
    self::loadCache();
    if($pathBegin)
    {
      foreach(self::$cache as $path => $cache)
      {
        if(strpos($path, $pathBegin) === 0)
          unset(self::$cache[$path]);
      }
    }
    else
    {
      self::$cache = array();
    }
    rex_file::putCache(rex_path::cache('install/webservice.cache'), self::$cache);
  }

  static private function getCache($path)
  {
    self::loadCache();
    if(isset(self::$cache[$path]))
    {
      return self::$cache[$path]['data'];
    }
    return null;
  }

  static private function loadCache()
  {
    if(self::$cache === null)
    {
      foreach((array) rex_file::getCache(rex_path::cache('install/webservice.cache')) as $path => $cache)
      {
        if($cache['stamp'] > time() - self::REFRESH_CACHE)
          self::$cache[$path] = $cache;
      }
    }
  }

  static private function setCache($path, $data)
  {
    self::$cache[$path]['stamp'] = time();
    self::$cache[$path]['data'] = $data;
    rex_file::putCache(rex_path::cache('install/webservice.cache'), self::$cache);
  }
}