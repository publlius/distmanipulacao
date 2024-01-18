<?php

class EstoqueList extends TPage
{
    private $form; // form
    private $datagrid; // listing
    private $pageNavigation;
    private $loaded;
    private static $database = 'entrega';
    private static $activeRecord = 'Estoque';
    private static $primaryKey = 'id';
    private static $formName = 'formList_Estoque';

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
        $this->form->setFormTitle("Listagem de estoques");


        $id = new TEntry('id');
        $numero_formula = new TEntry('numero_formula');
        $cliente = new TEntry('cliente');
        $data_emissao = new TDate('data_emissao');
        $previsao_entrega = new TDate('previsao_entrega');
        $valor = new TNumeric('valor', '2', ',', '.' );
        $situacao_id = new TDBCombo('situacao_id', 'entrega', 'Situacao', 'id', '{id}','id asc'  );

        $data_emissao->setDatabaseMask('yyyy-mm-dd');
        $previsao_entrega->setDatabaseMask('yyyy-mm-dd');

        $data_emissao->setMask('dd/mm/yyyy');
        $previsao_entrega->setMask('dd/mm/yyyy');

        $id->setSize(100);
        $valor->setSize('70%');
        $cliente->setSize('70%');
        $data_emissao->setSize(110);
        $situacao_id->setSize('70%');
        $numero_formula->setSize('70%');
        $previsao_entrega->setSize(110);

        $row1 = $this->form->addFields([new TLabel("Id:", null, '14px', null)],[$id]);
        $row2 = $this->form->addFields([new TLabel("Numero formula:", null, '14px', null)],[$numero_formula]);
        $row3 = $this->form->addFields([new TLabel("Cliente:", null, '14px', null)],[$cliente]);
        $row4 = $this->form->addFields([new TLabel("Data emissao:", null, '14px', null)],[$data_emissao]);
        $row5 = $this->form->addFields([new TLabel("Previsao entrega:", null, '14px', null)],[$previsao_entrega]);
        $row6 = $this->form->addFields([new TLabel("Valor:", null, '14px', null)],[$valor]);
        $row7 = $this->form->addFields([new TLabel("Situacao id:", null, '14px', null)],[$situacao_id]);

        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue(__CLASS__.'_filter_data') );

        $btn_onsearch = $this->form->addAction("Buscar", new TAction([$this, 'onSearch']), 'fa:search #ffffff');
        $btn_onsearch->addStyleClass('btn-primary'); 

        $btn_onexportcsv = $this->form->addAction("Exportar como CSV", new TAction([$this, 'onExportCsv']), 'fa:file-text-o #000000');

        $btn_onshow = $this->form->addAction("Cadastrar", new TAction(['EstoqueForm', 'onShow']), 'fa:plus #69aa46');

        // creates a Datagrid
        $this->datagrid = new TDataGrid;
        $this->datagrid = new BootstrapDatagridWrapper($this->datagrid);

        $this->datagrid->style = 'width: 100%';
        $this->datagrid->setHeight(320);

        $column_id = new TDataGridColumn('id', "Id", 'center' , '70px');
        $column_numero_formula = new TDataGridColumn('numero_formula', "Numero formula", 'left');
        $column_cliente = new TDataGridColumn('cliente', "Cliente", 'left');
        $column_data_emissao = new TDataGridColumn('data_emissao', "Data emissao", 'left');
        $column_previsao_entrega = new TDataGridColumn('previsao_entrega', "Previsao entrega", 'left');
        $column_valor = new TDataGridColumn('valor', "Valor", 'left');
        $column_situacao_id = new TDataGridColumn('situacao_id', "Situacao id", 'left');

        $order_id = new TAction(array($this, 'onReload'));
        $order_id->setParameter('order', 'id');
        $column_id->setAction($order_id);

        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_numero_formula);
        $this->datagrid->addColumn($column_cliente);
        $this->datagrid->addColumn($column_data_emissao);
        $this->datagrid->addColumn($column_previsao_entrega);
        $this->datagrid->addColumn($column_valor);
        $this->datagrid->addColumn($column_situacao_id);

        $action_onEdit = new TDataGridAction(array('EstoqueForm', 'onEdit'));
        $action_onEdit->setUseButton(false);
        $action_onEdit->setButtonClass('btn btn-default btn-sm');
        $action_onEdit->setLabel("Editar");
        $action_onEdit->setImage('fa:pencil-square-o #478fca');
        $action_onEdit->setField(self::$primaryKey);

        $this->datagrid->addAction($action_onEdit);

        $action_onDelete = new TDataGridAction(array('EstoqueList', 'onDelete'));
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
        $container->add(TBreadCrumb::create(['Movimentações','Estoques']));
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
                $object = new Estoque($key, FALSE); 

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

        if (isset($data->numero_formula) AND ( (is_scalar($data->numero_formula) AND $data->numero_formula !== '') OR (is_array($data->numero_formula) AND (!empty($data->numero_formula)) )) )
        {

            $filters[] = new TFilter('numero_formula', '=', $data->numero_formula);// create the filter 
        }

        if (isset($data->cliente) AND ( (is_scalar($data->cliente) AND $data->cliente !== '') OR (is_array($data->cliente) AND (!empty($data->cliente)) )) )
        {

            $filters[] = new TFilter('cliente', 'like', "%{$data->cliente}%");// create the filter 
        }

        if (isset($data->data_emissao) AND ( (is_scalar($data->data_emissao) AND $data->data_emissao !== '') OR (is_array($data->data_emissao) AND (!empty($data->data_emissao)) )) )
        {

            $filters[] = new TFilter('data_emissao', '=', $data->data_emissao);// create the filter 
        }

        if (isset($data->previsao_entrega) AND ( (is_scalar($data->previsao_entrega) AND $data->previsao_entrega !== '') OR (is_array($data->previsao_entrega) AND (!empty($data->previsao_entrega)) )) )
        {

            $filters[] = new TFilter('previsao_entrega', '=', $data->previsao_entrega);// create the filter 
        }

        if (isset($data->valor) AND ( (is_scalar($data->valor) AND $data->valor !== '') OR (is_array($data->valor) AND (!empty($data->valor)) )) )
        {

            $filters[] = new TFilter('valor', '=', $data->valor);// create the filter 
        }

        if (isset($data->situacao_id) AND ( (is_scalar($data->situacao_id) AND $data->situacao_id !== '') OR (is_array($data->situacao_id) AND (!empty($data->situacao_id)) )) )
        {

            $filters[] = new TFilter('situacao_id', '=', $data->situacao_id);// create the filter 
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

            // creates a repository for Estoque
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

