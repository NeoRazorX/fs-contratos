<?php
namespace FacturaScripts\Plugins\Contratos\Controller;

use FacturaScripts\Core\Base\DataBase;
use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Core\DataSrc\Impuestos;
use FacturaScripts\Core\Lib\ExtendedController\ListController;
use FacturaScripts\Plugins\Contratos\Model\ContratoServicio;

class ListContratoServicio extends ListController
{
    public function getPageData() {
        $data = parent::getPageData();
        $data["title"] = "Contratos";
        $data["menu"] = "sales";
        $data["icon"] = "fas fa-file-signature";
        return $data;
    }


    protected function createViews() {

        $this->createViewsContratoServicio();
    }


    /**
     * Run the actions that alter data before reading it.
     *
     * @param string $action
     *
     * @return bool
     */
    protected function execPreviousAction($action)
    {
        switch ($action) {
            case 'renew':
                return $this->renewAction();
        }

        return parent::execPreviousAction($action);
    }



    protected function createViewsContratoServicio($viewName = "ListContratoServicio")
    {
        $this->addView($viewName, "ContratoServicio", "Contratos");

        // Para ordenar
        $this->addOrderBy($viewName, ["fecha_renovacion"], "fecha_renovacion");

        // filtro general
        $this->addSearchFields($viewName, ['titulo', 'observaciones']);

        // filters
        $this->addFilterAutocomplete($viewName, 'codcliente', 'cliente', 'codcliente', 'clientes', 'codcliente', 'nombre');

        //        Para que nos e vea siempre la sección de filtros abierta
        $this->addFilterSelectWhere($viewName, 'suspendido', [
            ['label' => 'Activo', 'where' => [new DataBaseWhere('suspendido', false)]],
            ['label' => 'Suspendido', 'where' => [new DataBaseWhere('suspendido', true)]],
        ]);


        $this->addFilterSelect($viewName, 'agrupacion', 'agrupacion', 'agrupacion', ContratoServicio::getAgrupacionToDropDown());

        $this->addRenewButton($viewName);
    }


    /**
     * Add an modal button for renumber entries
     *
     * @param string $viewName
     */
    protected function addRenewButton(string $viewName)
    {
        $this->addButton($viewName, [
            'action' => 'renew',
            'icon' => 'fas fa-plus',
            'label' => 'Renovar',
            'type' => 'modal',
        ]);
    }


    /**
     * @return bool
     */
    protected function renewAction(): bool
    {

        $codes = explode(',', $this->request->request->get('code'));

        if (false === is_array($codes)) {
            $this->toolBox()->i18nLog()->warning('no-selected-item');
            return true;
        }

        $date = $this->request->request->get('date');
        $res = [];

        foreach ($codes as $code){
            $res[$code] = ContratoServicio::renewService($code, $date);
        }

        $this->redirect('RenewContratoServicio?'.http_build_query(array('params' => $res)));

        return true;
    }

}
