<?php
/**
 * @file DescribeJournalsPlugin.inc.php
 *
 * Copyright (c) 2017-2021 Simon Fraser University
 * Copyright (c) 2017-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class DescribeJournalsPlugin
 * @brief Plugin class for the DescribeJournals plugin.
 */
import('lib.pkp.classes.plugins.GenericPlugin');
class DescribeJournalsPlugin extends GenericPlugin {

	var $optionsindexed_in = [	
		'Biblat',
		'CLASE',
		'Nucleo Básico',
		'BINPAR',
		'DIALNET',
		'DOAJ',
		'Latindex catálogo', 
		'MIAR',
		'RedALyC',
		'REDIB',
		'SciELO',
		'SciELO Citation Index',
		'SCOPUS',
		'WoS' ]  ; 
public function getdisplayname() {
	return 'Describe journal' ; 
}
public function getdescription() {
	return 'Describe journal, extra info' ; 
}
	public function register($category, $path, $mainContextId = null) {
		$success = parent::register($category, $path);
		if ($success && $this->getEnabled()) {

      // Use a hook to extend the context entity's schema
      HookRegistry::register('Schema::get::context', array($this, 'addToSchema'));

      // Use a hook to add a field to the masthead form in the journal/press settings.
      HookRegistry::register('Form::config::before', array($this, 'addToForm'));
		}
		return $success;
  }

  /**
   * Extend the context entity's schema with an building property
   */
  public function addToSchema($hookName, $args) {
		$schema = $args[0];
		$schema->properties->building = (object) [
			'type' => 'string',
			'apiSummary' => true,
			'multilingual' => false,
			'validation' => ['nullable']
		];
		$schema->properties->doi_asigned = (object) [
			'type' => 'string',
			'apiSummary' => true,
			'multilingual' => false,
			'validation' => ['nullable']
		];
		$schema->properties->url_old_archives = (object) [
			'type' => 'string',
			'apiSummary' => true,
			'multilingual' => false,
			'validation' => ['nullable']
		];
		$schema->properties->disciplines = (object) [
			'type' => 'string',
			'apiSummary' => true,
			'multilingual' => false,
			'validation' => ['nullable']
		];

    $schema->properties->indexed_in = (object) [
        'type' => 'array',
        //'apiSummary' => true,
        'multilingual' => false,
        'validation' => ['nullable'],
		'items' => (object) [
			'type' => 'string'
			]
    ];
//dump($schema->properties->indexed_in) ;
		return false;
  }

  /**
   * Extend the masthead form to add an building input field
   * in the journal/press settings
   */
	public function addtoForm($hookName, $form) {

    // Only modify the masthead form
		if (!defined('FORM_MASTHEAD') || $form->id !== FORM_MASTHEAD) {
			return;
    }

    // Don't do anything at the site-wide level
		$context = Application::get()->getRequest()->getContext();
		if (!$context) {
			return;
    }
	$form->addGroup([
		'id' => 'sid',
		'label' => 'Información SID',
	]) ; 
    // Add a field to the form
		$form->addField(new \PKP\components\forms\FieldText('building', [
			'label' => 'Dependencia',
			'groupId' => 'sid',
			'isRequired'=>true,
			'value' => $context->getData('building'),
			'description'=>'se utiliza para colocar la facultad a la que pertenece, colocar nombre completo. Ej: Facultad de Odontología'
		]));
		$form->addField(new \PKP\components\forms\FieldText('disciplines', [
			'label' => 'Disciplinas',
			'groupId' => 'sid',//'publishing',
			'isRequired'=>true,
			'value' => $context->getData('disciplines'),
			'description'=>'Separar por comas'
		]));
		
		
	 //
	 $values=[] ; 
foreach($this->optionsindexed_in as $k => $v) {
    $values[] = ['value' => str_replace(' ','_',strtolower($v)) , 'label' => $v ,'name' => $v] ;
}

		$form->addField(new \PKP\components\forms\FieldOptions('indexed_in', [
			'label' => 'Indexado en' ,
			'groupId' => 'sid',//'publishing',
			'type'=>'checkbox' ,
			'isMultilingual' => false,
			'options' => $values , 
			'value' => $context->getData('indexed_in') ? $context->getData('indexed_in') : [],
			]
		)
	);	
		
	$form->addField(new \PKP\components\forms\FieldOptions('doi_asigned', [
		'label' => 'Registra DOIs',
		'groupId' => 'sid',
		'type'=>'radio', 
		'isRequired'=>true,
		'options'=>[['value'=>'false','label'=>'No'] , ['value'=>'true','label'=>'Si'] ]  , 
		'value' => $context->getData('doi_asigned'),
		'description'=>'¿Se le asignan DOIs a los artículos seleccionados ? '
	]));
	$form->addField(new \PKP\components\forms\FieldText('url_old_archives', [
		'label' => 'URL histórico de artículos',
		'groupId' => 'sid',
		'inputType'=>'URL' , 
		'isRequired'=>false,
		'value' => $context->getData('url_old_archives'),
		'description'=>'url completa para ver el archivo de los artículos anteriores a la migración '
	]));
	
	return false;
	}
}
