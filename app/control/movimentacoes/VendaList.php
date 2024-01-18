<?php

class VendaList extends TPage
{
    private $form; // form
    private $datagrid; // listing
    private $pageNavigation;
    private $loaded;
    private $filter_criteria;
    private static $database = 'entrega';
    private static $activeRecord = 'Venda';
    private static $primaryKey = 'id';
    private static $formName = 'formList_Venda';

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
        $this->form->setFormTitle("Listagem de vendas");

        $id = new TEntry('id');
        $data_venda = new TDate('data_venda');
        $romaneio_id = new TDBCombo('romaneio_id', 'entrega', 'Romaneio', 'id', '{cliente} - {numero_venda} ','id asc'  );
        $numero_formula = new TEntry('numero_formula');
        $vendido_por_id = new TDBCombo('vendido_por_id', 'permission', 'SystemUsers', 'id', '{login}','login asc'  );

        $data_venda->setDatabaseMask('yyyy-mm-dd');
        $data_venda->setMask('dd/mm/yyyy');

        $id->setSize(100);
        $data_venda->setSize(110);
        $romaneio_id->setSize('70%');
        $numero_formula->setSize('70%');
        $vendido_por_id->setSize('70%');

        $row1 = $this->form->addFields([new TLabel("Id:", null, '14px', null)],[$id],[new TLabel("Data venda:", null, '14px', null)],[$data_venda]);
        $row2 = $this->form->addFields([new TLabel("Romaneio:", null, '14px', null)],[$romaneio_id],[new TLabel("Numero formula:", null, '14px', null)],[$numero_formula]);
        $row3 = $this->form->addFields([new TLabel("Vendedor:", null, '14px', null)],[$vendido_por_id]);

        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue(__CLASS__.'_filter_data') );

        $btn_onsearch = $this->form->addAction("Buscar", new TAction([$this, 'onSearch']), 'fa:search #ffffff');
        $btn_onsearch->addStyleClass('btn-primary'); 

        $btn_onexportcsv = $this->form->addAction("Exportar como CSV", new TAction([$this, 'onExportCsv']), 'fa:file-text-o #000000');

        $btn_onshow = $this->form->addAction("Cadastrar", new TAction(['VendaForm', 'onShow']), 'fa:plus #69aa46');

        // creates a Datagrid
        $this->datagrid = new TDataGrid;
        $this->datagrid = new BootstrapDatagridWrapper($this->datagrid);
        $this->filter_criteria = new TCriteria;

        $this->datagrid->style = 'width: 100%';
        $this->datagrid->setHeight(320);

        $column_romaneio_numero_venda_romaneio_cliente = new TDataGridColumn('{romaneio->numero_venda} - {romaneio->cliente}', "Romaneio", 'left');
        $column_vendido_por_login_transformed = new TDataGridColumn('vendido_por->login', "Vendido por", 'left');
        $column_entregue_por_login_transformed = new TDataGridColumn('entregue_por->login', "Entregue por", 'left');
        $column_cf = new TDataGridColumn('cf', "Cf", 'left');
        $column_data_venda_transformed = new TDataGridColumn('data_venda', "Data venda", 'right');
        $column_valor_formula_transformed = new TDataGridColumn('valor_formula', "R$ fórmula", 'right');
        $column_valor_venda_transformed = new TDataGridColumn('valor_venda', "R$ venda", 'right');
        $column_calculated_1 = new TDataGridColumn('=( ({valor_formula} - {valor_venda} ) / ( {valor_formula})*100 )', "Desconto %", 'right');
        $column_catalogo_transformed = new TDataGridColumn('catalogo', "Catálogo", 'center');
        $column_forma_pagamento_descricao_transformed = new TDataGridColumn('forma_pagamento->descricao', "Pagamento", 'left');

        $column_valor_formula_transformed->setTotalFunction( function($values) { 
            return array_sum((array) $values); 
        }); 

        $column_valor_venda_transformed->setTotalFunction( function($values) { 
            return array_sum((array) $values); 
        }); 

        $column_calculated_1->setTotalFunction( function($values) { 
            return array_sum((array) $values) / count((array) $values); 
        }); 

        $column_vendido_por_login_transformed->setTransformer(function($value, $object, $row) 
        {
            if($value)
            {
                return mb_strtoupper($value);
            }
        });

        $column_entregue_por_login_transformed->setTransformer(function($value, $object, $row) 
        {
            if($value)
            {
                return mb_strtoupper($value);
            }
        });

        $column_data_venda_transformed->setTransformer(function($value, $object, $row) 
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

        $column_valor_formula_transformed->setTransformer(function($value, $object, $row) 
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

        $column_catalogo_transformed->setTransformer(function($value, $object, $row) 
        {
            if($value)
            {
                return mb_strtoupper($value);
            }
        });

        $column_forma_pagamento_descricao_transformed->setTransformer(function($value, $object, $row) 
        {
            if($value)
            {
                return mb_strtoupper($value);
            }
        });        

        $this->datagrid->addColumn($column_romaneio_numero_venda_romaneio_cliente);
        $this->datagrid->addColumn($column_vendido_por_login_transformed);
        $this->datagrid->addColumn($column_entregue_por_login_transformed);
        $this->datagrid->addColumn($column_cf);
        $this->datagrid->addColumn($column_data_venda_transformed);
        $this->datagrid->addColumn($column_valor_formula_transformed);
        $this->datagrid->addColumn($column_valor_venda_transformed);
        $this->datagrid->addColumn($column_calculated_1);
        $this->datagrid->addColumn($column_catalogo_transformed);
        $this->datagrid->addColumn($column_forma_pagamento_descricao_transformed);

        $action_onEdit = new TDataGridAction(array('VendaForm', 'onEdit'));
        $action_onEdit->setUseButton(false);
        $action_onEdit->setButtonClass('btn btn-default btn-sm');
        $action_onEdit->setLabel("Editar");
        $action_onEdit->setImage('fa:pencil-square-o #478fca');
        $action_onEdit->setField(self::$primaryKey);

        $this->datagrid->addAction($action_onEdit);

        $action_onDelete = new TDataGridAction(array('VendaList', 'onDelete'));
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
        $container->add(TBreadCrumb::create(["Movimentações","Vendas"]));
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
                $object = new Venda($key, FALSE); 

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

        if (isset($data->id) AND ( (is_scalar($data->id) AND $data->id !== '') OR (is_array($data->id) AND (!empty($data->id)) )) )
        {

            $filters[] = new TFilter('id', '=', $data->id);// create the filter 
        }

        if (isset($data->data_venda) AND ( (is_scalar($data->data_venda) AND $data->data_venda !== '') OR (is_array($data->data_venda) AND (!empty($data->data_venda)) )) )
        {

            $filters[] = new TFilter('data_venda', '=', $data->data_venda);// create the filter 
        }

        if (isset($data->romaneio_id) AND ( (is_scalar($data->romaneio_id) AND $data->romaneio_id !== '') OR (is_array($data->romaneio_id) AND (!empty($data->romaneio_id)) )) )
        {

            $filters[] = new TFilter('romaneio_id', '=', $data->romaneio_id);// create the filter 
        }

        if (isset($data->numero_formula) AND ( (is_scalar($data->numero_formula) AND $data->numero_formula !== '') OR (is_array($data->numero_formula) AND (!empty($data->numero_formula)) )) )
        {

            $filters[] = new TFilter('numero_formula', '=', $data->numero_formula);// create the filter 
        }

        if (isset($data->vendido_por_id) AND ( (is_scalar($data->vendido_por_id) AND $data->vendido_por_id !== '') OR (is_array($data->vendido_por_id) AND (!empty($data->vendido_por_id)) )) )
        {

            $filters[] = new TFilter('vendido_por_id', '=', $data->vendido_por_id);// create the filter 
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

            // creates a repository for Venda
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

