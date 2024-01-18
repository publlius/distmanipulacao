<?php

class RomaneioList extends TPage
{
    private $form; // form
    private $datagrid; // listing
    private $pageNavigation;
    private $loaded;
    private $filter_criteria;
    private static $database = 'entrega';
    private static $activeRecord = 'Romaneio';
    private static $primaryKey = 'id';
    private static $formName = 'formList_Romaneio';

    /**
     * Class constructor
     * Creates the page, the form and the listing
     */
    public function __construct()
    {
        parent::__construct();
        // creates the form
        $this->form = new BootstrapFormBuilder(self::$formName);

        // define the form title
        $this->form->setFormTitle("Listagem de romaneios");

        $farmacia_id = new TDBCombo('farmacia_id', 'permission', 'SystemUnit', 'id', '{name}','name asc'  );
        $cliente = new TEntry('cliente');
        $numero_venda = new TEntry('numero_venda');
        $emissao_venda = new TDate('emissao_venda');
        $previsao_entrega = new TDate('previsao_entrega');

        $emissao_venda->setDatabaseMask('yyyy-mm-dd');
        $previsao_entrega->setDatabaseMask('yyyy-mm-dd');

        $emissao_venda->setMask('dd/mm/yyyy');
        $previsao_entrega->setMask('dd/mm/yyyy');

        $cliente->setSize('70%');
        $farmacia_id->setSize('70%');
        $emissao_venda->setSize(110);
        $numero_venda->setSize('70%');
        $previsao_entrega->setSize(110);

        $row1 = $this->form->addFields([new TLabel("Farmácia:", null, '14px', null)],[$farmacia_id]);
        $row2 = $this->form->addFields([new TLabel("Cliente:", null, '14px', null)],[$cliente],[new TLabel("Numero venda:", null, '14px', null)],[$numero_venda]);
        $row3 = $this->form->addFields([new TLabel("Emissão venda:", null, '14px', null)],[$emissao_venda],[new TLabel("Previsão entrega:", null, '14px', null)],[$previsao_entrega]);

        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue(__CLASS__.'_filter_data') );

        $btn_onsearch = $this->form->addAction("Buscar", new TAction([$this, 'onSearch']), 'fa:search #ffffff');
        $btn_onsearch->addStyleClass('btn-primary'); 

        $btn_onexportcsv = $this->form->addAction("Exportar como CSV", new TAction([$this, 'onExportCsv']), 'fa:file-text-o #000000');

        $btn_onshow = $this->form->addAction("Importar", new TAction(['RomaneioForm', 'onShow']), 'fa:upload #69aa46');

        // creates a Datagrid
        $this->datagrid = new TDataGrid;
        $this->datagrid = new BootstrapDatagridWrapper($this->datagrid);
        $this->filter_criteria = new TCriteria;

        $this->datagrid->style = 'width: 100%';
        $this->datagrid->setHeight(320);

        $column_numero_venda = new TDataGridColumn('numero_venda', "Romaneio", 'left');
        $column_farmacia_name = new TDataGridColumn('farmacia->name', "Farmácia", 'left');
        $column_cliente = new TDataGridColumn('cliente', "Cliente", 'left');
        $column_emissao_venda_transformed = new TDataGridColumn('emissao_venda', "Emissão venda", 'center');
        $column_previsao_entrega_transformed = new TDataGridColumn('previsao_entrega', "Previsao entrega", 'center');
        $column_previsao_entrega_hora = new TDataGridColumn('previsao_entrega_hora', "Previsão hora", 'left');
        $column_valor_venda_transformed = new TDataGridColumn('valor_venda', "Valor venda", 'left');

        $column_valor_venda_transformed->setTotalFunction( function($values) { 
            return array_sum((array) $values); 
        }); 

        $column_emissao_venda_transformed->setTransformer(function($value, $object, $row) 
        {
            if(!empty(trim($value)))
            {
                try
                {
                    $date = new DateTime($value);
                    return $date->format('d/m/Y');
                }
                catch (Exception $e)
                {
                    return $value;
                }
            }
        });

        $column_previsao_entrega_transformed->setTransformer(function($value, $object, $row) 
        {
            if(!empty(trim($value)))
            {
                try
                {
                    $date = new DateTime($value);
                    return $date->format('d/m/Y');
                }
                catch (Exception $e)
                {
                    return $value;
                }
            }
        });

        $column_valor_venda_transformed->setTransformer(function($value, $object, $row) 
        {
            if(!$value)
            {
                $value = 0;
            }

            if(is_numeric($value))
            {
                return "R$ " . number_format($value, 2, ",", ".");
            }
            else
            {
                return $value;
            }
        });        

        $order_numero_venda = new TAction(array($this, 'onReload'));
        $order_numero_venda->setParameter('order', 'numero_venda');
        $column_numero_venda->setAction($order_numero_venda);
        $order_cliente = new TAction(array($this, 'onReload'));
        $order_cliente->setParameter('order', 'cliente');
        $column_cliente->setAction($order_cliente);
        $order_emissao_venda_transformed = new TAction(array($this, 'onReload'));
        $order_emissao_venda_transformed->setParameter('order', 'emissao_venda');
        $column_emissao_venda_transformed->setAction($order_emissao_venda_transformed);
        $order_previsao_entrega_transformed = new TAction(array($this, 'onReload'));
        $order_previsao_entrega_transformed->setParameter('order', 'previsao_entrega');
        $column_previsao_entrega_transformed->setAction($order_previsao_entrega_transformed);

        $this->datagrid->addColumn($column_numero_venda);
        $this->datagrid->addColumn($column_farmacia_name);
        $this->datagrid->addColumn($column_cliente);
        $this->datagrid->addColumn($column_emissao_venda_transformed);
        $this->datagrid->addColumn($column_previsao_entrega_transformed);
        $this->datagrid->addColumn($column_previsao_entrega_hora);
        $this->datagrid->addColumn($column_valor_venda_transformed);

        $action_onEdit = new TDataGridAction(array('RomaneioForm', 'onEdit'));
        $action_onEdit->setUseButton(false);
        $action_onEdit->setButtonClass('btn btn-default btn-sm');
        $action_onEdit->setLabel("Editar");
        $action_onEdit->setImage('fa:pencil-square-o #478fca');
        $action_onEdit->setField(self::$primaryKey);

        $this->datagrid->addAction($action_onEdit);

        $action_onDelete = new TDataGridAction(array('RomaneioList', 'onDelete'));
        $action_onDelete->setUseButton(false);
        $action_onDelete->setButtonClass('btn btn-default btn-sm');
        $action_onDelete->setLabel("Excluir");
        $action_onDelete->setImage('fa:trash-o #dd5a43');
        $action_onDelete->setField(self::$primaryKey);

        $this->datagrid->addAction($action_onDelete);

        // create the datagrid model
        $this->datagrid->createModel();

        // creates the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->enableCounters();
        $this->pageNavigation->setAction(new TAction(array($this, 'onReload')));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());

        $panel = new TPanelGroup;
        $panel->add($this->datagrid);

        $panel->addFooter($this->pageNavigation);

        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->add(TBreadCrumb::create(["Movimentações","Romaneios"]));
        $container->add($this->form);
        $container->add($panel);

        parent::add($container);

    }

    public function onExportCsv($param = null) 
    {
        try
        {
            $this->onSearch();

            TTransaction::open(self::$database); // open a transaction
            $repository = new TRepository(self::$activeRecord); // creates a repository for Customer
            $criteria = new TCriteria; // creates a criteria

            if($filters = TSession::getValue(__CLASS__.'_filters'))
            {
                foreach ($filters as $filter) 
                {
                    $criteria->add($filter);       
                }
            }

            $records = $repository->load($criteria); // load the objects according to criteria
            if ($records)
            {
                $file = 'tmp/'.uniqid().'.csv';
                $handle = fopen($file, 'w');
                $columns = $this->datagrid->getColumns();

                $csvColumns = [];
                foreach($columns as $column)
                {
                    $csvColumns[] = $column->getLabel();
                }
                fputcsv($handle, $csvColumns, ';');

                foreach ($records as $record)
                {
                    $csvColumns = [];
                    foreach($columns as $column)
                    {
                        $name = $column->getName();
                        $csvColumns[] = $record->{$name};
                    }
                    fputcsv($handle, $csvColumns, ';');
                }
                fclose($handle);

                TPage::openFile($file);
            }
            else
            {
                new TMessage('info', _t('No records found'));       
            }

            TTransaction::close(); // close the transaction
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
        }
    }

    public function onDelete($param = null) 
    { 
        if(isset($param['delete']) && $param['delete'] == 1)
        {
            try
            {
                // get the paramseter $key
                $key = $param['key'];
                // open a transaction with database
                TTransaction::open(self::$database);

                // instantiates object
                $object = new Romaneio($key, FALSE); 

                // deletes the object from the database
                $object->delete();

                // close the transaction
                TTransaction::close();

                // reload the listing
                $this->onReload( $param );
                // shows the success message
                new TMessage('info', AdiantiCoreTranslator::translate('Record deleted'));
            }
            catch (Exception $e) // in case of exception
            {
                // shows the exception error message
                new TMessage('error', $e->getMessage());
                // undo all pending operations
                TTransaction::rollback();
            }
        }
        else
        {
            // define the delete action
            $action = new TAction(array($this, 'onDelete'));
            $action->setParameters($param); // pass the key paramseter ahead
            $action->setParameter('delete', 1);
            // shows a dialog to the user
            new TQuestion(AdiantiCoreTranslator::translate('Do you really want to delete ?'), $action);   
        }
    }

    /**
     * Register the filter in the session
     */
    public function onSearch()
    {
        // get the search form data
        $data = $this->form->getData();
        $filters = [];

        TSession::setValue(__CLASS__.'_filter_data', NULL);
        TSession::setValue(__CLASS__.'_filters', NULL);

        if (isset($data->farmacia_id) AND ( (is_scalar($data->farmacia_id) AND $data->farmacia_id !== '') OR (is_array($data->farmacia_id) AND (!empty($data->farmacia_id)) )) )
        {

            $filters[] = new TFilter('farmacia_id', 'like', "%{$data->farmacia_id}%");// create the filter 
        }

        if (isset($data->cliente) AND ( (is_scalar($data->cliente) AND $data->cliente !== '') OR (is_array($data->cliente) AND (!empty($data->cliente)) )) )
        {

            $filters[] = new TFilter('cliente', 'like', "%{$data->cliente}%");// create the filter 
        }

        if (isset($data->numero_venda) AND ( (is_scalar($data->numero_venda) AND $data->numero_venda !== '') OR (is_array($data->numero_venda) AND (!empty($data->numero_venda)) )) )
        {

            $filters[] = new TFilter('numero_venda', '=', $data->numero_venda);// create the filter 
        }

        if (isset($data->emissao_venda) AND ( (is_scalar($data->emissao_venda) AND $data->emissao_venda !== '') OR (is_array($data->emissao_venda) AND (!empty($data->emissao_venda)) )) )
        {

            $filters[] = new TFilter('emissao_venda', '=', $data->emissao_venda);// create the filter 
        }

        if (isset($data->previsao_entrega) AND ( (is_scalar($data->previsao_entrega) AND $data->previsao_entrega !== '') OR (is_array($data->previsao_entrega) AND (!empty($data->previsao_entrega)) )) )
        {

            $filters[] = new TFilter('previsao_entrega', '=', $data->previsao_entrega);// create the filter 
        }

        $param = array();
        $param['offset']     = 0;
        $param['first_page'] = 1;

        // fill the form with data again
        $this->form->setData($data);

        // keep the search data in the session
        TSession::setValue(__CLASS__.'_filter_data', $data);
        TSession::setValue(__CLASS__.'_filters', $filters);

        $this->onReload($param);
    }

    /**
     * Load the datagrid with data
     */
    public function onReload($param = NULL)
    {
        try
        {
            // open a transaction with database 'entrega'
            TTransaction::open(self::$database);

            // creates a repository for Romaneio
            $repository = new TRepository(self::$activeRecord);
            $limit = 20;

            $criteria = $this->filter_criteria;

            if (empty($param['order']))
            {
                $param['order'] = 'id';    
            }

            if (empty($param['direction']))
            {
                $param['direction'] = 'desc';
            }

            $criteria->setProperties($param); // order, offset
            $criteria->setProperty('limit', $limit);

            if($filters = TSession::getValue(__CLASS__.'_filters'))
            {
                foreach ($filters as $filter) 
                {
                    $criteria->add($filter);       
                }
            }

            // load the objects according to criteria
            $objects = $repository->load($criteria, FALSE);

            $this->datagrid->clear();
            if ($objects)
            {
                // iterate the collection of active records
                foreach ($objects as $object)
                {
                    // add the object inside the datagrid

                    $this->datagrid->addItem($object);

                }
            }

            // reset the criteria for record count
            $criteria->resetProperties();
            $count= $repository->count($criteria);

            $this->pageNavigation->setCount($count); // count of records
            $this->pageNavigation->setProperties($param); // order, page
            $this->pageNavigation->setLimit($limit); // limit

            // close the transaction
            TTransaction::close();
            $this->loaded = true;
        }
        catch (Exception $e) // in case of exception
        {
            // shows the exception error message
            new TMessage('error', $e->getMessage());
            // undo all pending operations
            TTransaction::rollback();
        }
    }

    public function onShow($param = null)
    {

    }

    /**
     * method show()
     * Shows the page
     */
    public function show()
    {
        // check if the datagrid is already loaded
        if (!$this->loaded AND (!isset($_GET['method']) OR !(in_array($_GET['method'],  array('onReload', 'onSearch')))) )
        {
            if (func_num_args() > 0)
            {
                $this->onReload( func_get_arg(0) );
            }
            else
            {
                $this->onReload();
            }
        }
        parent::show();
    }

}

