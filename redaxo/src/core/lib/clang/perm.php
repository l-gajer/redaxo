<?php

class rex_clang_perm extends rex_complex_perm
{
  public function hasPerm($clang)
  {
    return $this->hasAll() || in_array($clang, $this->perms);
  }

  public function count()
  {
    return $this->hasAll() ? rex_clang::count() : count($this->perms);
  }

  public function getClangs()
  {
    return $this->hasAll() ? rex_clang::getAllIds() : $this->perms;
  }

  static public function getFieldParams()
  {
    return array(
      'label' => rex_i18n::msg('clangs'),
      'all_label' => rex_i18n::msg('all_clangs'),
      'options' => rex_clang::getAll()
    );
  }
}