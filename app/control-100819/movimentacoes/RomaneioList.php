<?php

class RomaneioList extends TPage
{
    private $form; // form
    private $datagrid; // listing
    private $pageNavigation;
    private $loaded;
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


        $id = new TEntry('id');
        $farmacia_id = new TDBCombo('farmacia_id', 'entrega', 'Farmacia', 'id', '{id}','id asc'  );
        $numero_venda = new TEntry('numero_venda');
        $cliente = new TEntry('cliente');
        $emissao_venda = new TDate('emissao_venda');
        $previsao_entrega = new TDate('previsao_entrega');
        $previsao_entrega_hora = new TTime('previsao_entrega_hora');
        $valor_venda = new TNumeric('valor_venda', '2', ',', '.' );
        $valor_entrada = new TNumeric('valor_entrada', '2', ',', '.' );

        $emissao_venda->setDatabaseMask('yyyy-mm-dd');
        $previsao_entrega->setDatabaseMask('yyyy-mm-dd');

        $emissao_venda->setMask('dd/mm/yyyy');
        $previsao_entrega->setMask('dd/mm/yyyy');

        $id->setSize(100);
        $cliente->setSize('70%');
        $farmacia_id->setSize('70%');
        $emissao_venda->setSize(110);
        $valor_venda->setSize('70%');
        $numero_venda->setSize('70%');
        $valor_entrada->setSize('70%');
        $previsao_entrega->setSize(110);
        $previsao_entrega_hora->setSize(110);

        $row1 = $this->form->addFields([new TLabel("Id:", null, '14px', null)],[$id]);
        $row2 = $this->form->addFields([new TLabel("Farmacia id:", null, '14px', null)],[$farmacia_id]);
        $row3 = $this->form->addFields([new TLabel("Numero venda:", null, '14px', null)],[$numero_venda]);
        $row4 = $this->form->addFields([new TLabel("Cliente:", null, '14px', null)],[$cliente]);
        $row5 = $this->form->addFields([new TLabel("Emissao venda:", null, '14px', null)],[$emissao_venda]);
        $row6 = $this->form->addFields([new TLabel("Previsao entrega:", null, '14px', null)],[$previsao_entrega]);
        $row7 = $this->form->addFields([new TLabel("Previsao entrega hora:", null, '14px', null)],[$previsao_entrega_hora]);
        $row8 = $this->form->addFields([new TLabel("Valor venda:", null, '14px', null)],[$valor_venda]);
        $row9 = $this->form->addFields([new TLabel("Valor entrada:", null, '14px', null)],[$valor_entrada]);

        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue(__CLASS__.'_filter_data') );

        $btn_onsearch = $this->form->addAction("Buscar", new TAction([$this, 'onSearch']), 'fa:search #ffffff');
        $btn_onsearch->addStyleClass('btn-primary'); 

        $btn_onexportcsv = $this->form->addAction("Exportar como CSV", new TAction([$this, 'onExportCsv']), 'fa:file-text-o #000000');

        $btn_onshow = $this->form->addAction("Cadastrar", new TAction(['RomaneioForm', 'onShow']), 'fa:plus #69aa46');

        // creates a Datagrid
        $this->datagrid = new TDataGrid;
        $this->datagrid = new BootstrapDatagridWrapper($this->datagrid);

        $this->datagrid->style = 'width: 100%';
        $this->datagrid->setHeight(320);

        $column_id = new TDataGridColumn('id', "Id", 'center' , '70px');
        $column_farmacia_id = new TDataGridColumn('farmacia_id', "Farmacia id", 'left');
        $column_numero_venda = new TDataGridColumn('numero_venda', "Numero venda", 'left');
        $column_cliente = new TDataGridColumn('cliente', "Cliente", 'left');
        $column_emissao_venda = new TDataGridColumn('emissao_venda', "Emissao venda", 'left');
        $column_previsao_entrega = new TDataGridColumn('previsao_entrega', "Previsao entrega", 'left');
        $column_previsao_entrega_hora = new TDataGridColumn('previsao_entrega_hora', "Previsao entrega hora", 'left');
        $column_valor_venda = new TDataGridColumn('valor_venda', "Valor venda", 'left');
        $column_valor_entrada = new TDataGridColumn('valor_entrada', "Valor entrada", 'left');

        $order_id = new TAction(array($this, 'onReload'));
        $order_id->setParameter('order', 'id');
        $column_id->setAction($order_id);

        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_farmacia_id);
        $this->datagrid->addColumn($column_numero_venda);
        $this->datagrid->addColumn($column_cliente);
        $this->datagrid->addColumn($column_emissao_venda);
        $this->datagrid->addColumn($column_previsao_entrega);
        $this->datagrid->addColumn($column_previsao_entrega_hora);
        $this->datagrid->addColumn($column_valor_venda);
        $this->datagrid->addColumn($column_valor_entrada);

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
        $container->add(TBreadCrumb::create(['Movimentações','Romaneios']));
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

        if (isset($data->id) AND ( (is_scalar($data->id) AND $data->id !== '') OR (is_array($data->id) AND (!empty($data->id)) )) )
        {

            $filters[] = new TFilter('id', '=', $data->id);// create the filter 
        }

        if (isset($data->farmacia_id) AND ( (is_scalar($data->farmacia_id) AND $data->farmacia_id !== '') OR (is_array($data->farmacia_id) AND (!empty($data->farmacia_id)) )) )
        {

            $filters[] = new TFilter('farmacia_id', '=', $data->farmacia_id);// create the filter 
        }

        if (isset($data->numero_venda) AND ( (is_scalar($data->numero_venda) AND $data->numero_venda !== '') OR (is_array($data->numero_venda) AND (!empty($data->numero_venda)) )) )
        {

            $filters[] = new TFilter('numero_venda', '=', $data->numero_venda);// create the filter 
        }

        if (isset($data->cliente) AND ( (is_scalar($data->cliente) AND $data->cliente !== '') OR (is_array($data->cliente) AND (!empty($data->cliente)) )) )
        {

            $filters[] = new TFilter('cliente', 'like', "%{$data->cliente}%");// create the filter 
        }

        if (isset($data->emissao_venda) AND ( (is_scalar($data->emissao_venda) AND $data->emissao_venda !== '') OR (is_array($data->emissao_venda) AND (!empty($data->emissao_venda)) )) )
        {

            $filters[] = new TFilter('emissao_venda', '=', $data->emissao_venda);// create the filter 
        }

        if (isset($data->previsao_entrega) AND ( (is_scalar($data->previsao_entrega) AND $data->previsao_entrega !== '') OR (is_array($data->previsao_entrega) AND (!empty($data->previsao_entrega)) )) )
        {

            $filters[] = new TFilter('previsao_entrega', '=', $data->previsao_entrega);// create the filter 
        }

        if (isset($data->previsao_entrega_hora) AND ( (is_scalar($data->previsao_entrega_hora) AND $data->previsao_entrega_hora !== '') OR (is_array($data->previsao_entrega_hora) AND (!empty($data->previsao_entrega_hora)) )) )
        {

            $filters[] = new TFilter('previsao_entrega_hora', '=', $data->previsao_entrega_hora);// create the filter 
        }

        if (isset($data->valor_venda) AND ( (is_scalar($data->valor_venda) AND $data->valor_venda !== '') OR (is_array($data->valor_venda) AND (!empty($data->valor_venda)) )) )
        {

            $filters[] = new TFilter('valor_venda', '=', $data->valor_venda);// create the filter 
        }

        if (isset($data->valor_entrada) AND ( (is_scalar($data->valor_entrada) AND $data->valor_entrada !== '') OR (is_array($data->valor_entrada) AND (!empty($data->valor_entrada)) )) )
        {

            $filters[] = new TFilter('valor_entrada', '=', $data->valor_entrada);// create the filter 
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
            // creates a criteria
            $criteria = new TCriteria;

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

