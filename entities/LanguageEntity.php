<?php

namespace Wame\LanguageModule\Entities;

use Doctrine\ORM\Mapping as ORM;
use Wame\Core\Entities\BaseEntity;

/**
 * @ORM\Table(name="wame_language")
 * @ORM\Entity
 */
class LanguageEntity extends BaseEntity
{
	use \Wame\Core\Entities\Columns\Identifier;
	use \Wame\Core\Entities\Columns\Status;
	use \Wame\Core\Entities\Columns\Name;
	use \Wame\LanguageModule\Entities\Columns\Code;
	use \Wame\LanguageModule\Entities\Columns\Locale;
	use \Wame\Core\Entities\Columns\Sort;
	use \Wame\Core\Entities\Columns\Main;
    
}
