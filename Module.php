<?php
namespace ExtendedSiteDescription;

use Omeka\Form\Element\Asset;
use Omeka\Module\AbstractModule;
use Laminas\EventManager\Event;
use Laminas\EventManager\SharedEventManagerInterface;
use Laminas\Form\Form;
use Laminas\Mvc\Controller\AbstractController;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\View\Renderer\PhpRenderer;

class Module extends AbstractModule

{
    public function uninstall(ServiceLocatorInterface $serviceLocator)
    {
        $settings = $serviceLocator->get('Omeka\Settings');
        $settings->delete('extended_site_description_categories');
    }

    public function attachListeners(SharedEventManagerInterface $sharedEventManager)
    {
        $sharedEventManager->attach(
            'Omeka\Form\SiteSettingsForm',
            'form.add_elements',
            [$this, 'addToSiteSettingsForm']
        );

        $sharedEventManager->attach(
            'Omeka\Api\Representation\SiteRepresentation',
            'rep.resource.json',
            [$this, 'addSettingsToApi']
        );
        $sharedEventManager->attach(
            'Omeka\Form\SiteSettingsForm',
            'form.add_input_filters',
            [$this, 'filterSiteSettingsForm']
        );
    }

    public function filterSiteSettingsForm(Event $event)
{
    $inputFilter = $event->getParam('inputFilter');

$inputFilter->add([
    'name' => 'extended_site_description_categories',
    'required' => false,
    'filters' => [
        ['name' => \Laminas\Filter\StripTags::class],
        ['name' => \Laminas\Filter\StringTrim::class],
    ],
]);

$inputFilter->add([
    'name' => 'extended_site_description_linear',
    'required' => false,
]);

}

    public function getConfigForm(PhpRenderer $renderer)
    {
        $settings = $this->getServiceLocator()->get('Omeka\Settings');
        $categories = implode("\n", $settings->get('extended_site_description_categories', []));
        $form = new Form;
        $form->add([
            'type' => 'textarea',
            'name' => 'extended_site_description_categories',
            'options' => [
                'label' => 'Categories', // @translate
                'info' => 'Categories available to select, one per line', // @translate
            ],
            'attributes' => [
                'id' => 'extended_site_description_categories',
                'value' => $categories,
                'rows' => 10,
            ],
        ]);
        return $renderer->formCollection($form, false);
    }

    public function handleConfigForm(AbstractController $controller)
    {
        $rawCategories = $controller->params()->fromPost('extended_site_description_categories', '');
        $categories = array_unique(array_filter(array_map('trim', explode("\n", $rawCategories)), 'strlen'));
        $settings = $this->getServiceLocator()->get('Omeka\Settings');
        $settings->set('extended_site_description_categories', $categories);
    }

    public function addToSiteSettingsForm(Event $event)
    {
        $form = $event->getTarget();
        $siteSettings = $form->getSiteSettings();
        $settings = $this->getServiceLocator()->get('Omeka\Settings');
        $categories = $settings->get('extended_site_description_categories', []);
        
        $form->add([
            'type' => 'checkbox',
            'name' => 'extended_site_description_linear',
            'options' => [
                'label' => 'Linear', // @translate
                'use_hidden_element' => true,
                'checked_value' => '1',
                'unchecked_value' => '0',
            ],
            'attributes' => [
                'id' => 'extended_site_description_linear',
                'checked' => (bool) $siteSettings->get('extended_site_description_linear'),
            ],
        ]);
        
        $form->add([
            'type' => \Laminas\Form\Element\Select::class,
            'name' => 'extended_site_description_categories',
            'options' => [
                'label' => 'Categories', // @translate
                'value_options' => array_combine($categories, $categories),
            ],
            'attributes' => [
                'id' => 'extended_site_description_categories',
                'multiple' => true,
                'class' => 'chosen-select',
                'data-placeholder' => 'Select categories',
            ],
        ]);
        
        $form->get('extended_site_description_categories')->setValue(
            $siteSettings->get('extended_site_description_categories', [])
        );       
    }

    public function addSettingsToApi(Event $event)
    {
        $site = $event->getTarget();
        $siteId = $site->id();

        $services = $this->getServiceLocator();
        $api = $services->get('Omeka\ApiManager');
        $siteSettings = $services->build('Omeka\Settings\Site');
        $siteSettings->setTargetId($siteId);

        $jsonLd = $event->getParam('jsonLd');
        $jsonLd['extended_site_description_linear'] = (bool) $siteSettings->get('extended_site_description_linear');
        $jsonLd['extended_site_description_categories'] = $siteSettings->get('extended_site_description_categories', []);
    }
}