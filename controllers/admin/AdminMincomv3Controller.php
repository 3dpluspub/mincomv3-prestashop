<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

require_once dirname(__FILE__) . '/../../classes/Mincomv3Model.php';

class AdminMincomv3Controller extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'mincomv3';
        $this->className = 'Mincomv3Model';
        $this->lang = false;
        $this->addRowAction('edit');
        $this->addRowAction('delete');
        $this->bulk_actions = [
            'delete' => ['text' => $this->l('Supprimer les éléments sélectionnés'), 'confirm' => $this->l('Êtes-vous sûr ?')],
        ];

        $this->fields_list = [
            'id_mincomv3' => ['title' => $this->l('ID'), 'width' => 50],
            'postal_code' => ['title' => $this->l('Code Postal'), 'width' => 100],
            'city_name' => ['title' => $this->l('Ville'), 'width' => 200],
            'shipping_cost' => ['title' => $this->l('Frais de Port'), 'width' => 100, 'type' => 'price'],
            'free_shipping_amount' => ['title' => $this->l('Montant Franco'), 'width' => 150, 'type' => 'price'],
            'active' => ['title' => $this->l('Statut'), 'width' => 100, 'type' => 'bool', 'active' => 'status'],
            'date_add' => ['title' => $this->l('Date de création'), 'width' => 150, 'type' => 'datetime'],
        ];

        $this->fields_form = [
            'legend' => [
                'title' => $this->l('Gestion des Villes'),
                'icon' => 'icon-cogs',
            ],
            'input' => [
                [
                    'type' => 'text',
                    'label' => $this->l('Code Postal'),
                    'name' => 'postal_code',
                    'required' => false,
                    'col' => 3,
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Ville'),
                    'name' => 'city_name',
                    'required' => true,
                    'col' => 3,
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Frais de Port'),
                    'name' => 'shipping_cost',
                    'required' => true,
                    'col' => 3,
                    'hint' => $this->l('Ex: 5.00'),
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Montant Franco (Minimum)'),
                    'name' => 'free_shipping_amount',
                    'required' => true,
                    'col' => 3,
                    'hint' => $this->l('Ex: 50.00'),
                ],
                [
                    'type' => 'switch',
                    'label' => $this->l('Actif'),
                    'name' => 'active',
                    'required' => false,
                    'col' => 3,
                    'values' => [
                        [
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Oui'),
                        ],
                        [
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Non'),
                        ],
                    ],
                ],
            ],
            'submit' => [
                'title' => $this->l('Enregistrer'),
                'class' => 'btn btn-default pull-left',
            ],
        ];

        parent::__construct();
    }

    public function renderList(): string
    {
        $this->addFilter('a!id_mincomv3', 'id_mincomv3', 'id_mincomv3');
        $this->addFilter('a!postal_code', 'postal_code', 'postal_code');
        $this->addFilter('a!city_name', 'city_name', 'city_name');
        $this->addFilter('a!active', 'active', 'active');

        return parent::renderList();
    }

    public function renderForm(): string
    {
        if (!($obj = $this->loadObject(true))) {
            return '';
        }

        return parent::renderForm();
    }
}
