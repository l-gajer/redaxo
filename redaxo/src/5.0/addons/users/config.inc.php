<?php

rex_user::setRoleClass('rex_user_role');

rex_extension::register('COMPLEX_PERM_REMOVE_ITEM', 'rex_user_role::removeOrReplaceItem');
rex_extension::register('COMPLEX_PERM_REPLACE_ITEM', 'rex_user_role::removeOrReplaceItem');