<?php

class ProducaoForm extends TPage
{
    protected $form;
    private $formFields = [];
    private static $database = 'entrega';
    private static $activeRecord = 'Producao';
    private static $primaryKey = 'id';
    private static $formName = 'form_Producao';

    use Adianti\Base\AdiantiMasterDetailTrait;

    /**
     * Form constructor
     * @param $param Request
     */
    public function __construct( $param )
    {
        parent::__construct();

        // creates the form
        $this->form = new BootstrapFormBuilder(self::$formName);
        // define the form title
        $this->form->setFormTitle("Lançamento de produção (produto pronto)");


        $id = new TEntry('id');
        $data_producao = new TDate('data_producao');
        $observacao = new TEntry('observacao');
        $producao_detalhe_producao_produto_id = new TDBUniqueSearch('producao_detalhe_producao_produto_id', 'entrega', 'Produto', 'id', 'descricao','descricao asc'  );
        $producao_detalhe_producao_qtd = new TNumeric('producao_detalhe_producao_qtd', '0', '', '' );
        $producao_detalhe_producao_id = new THidden('producao_detalhe_producao_id');

        $data_producao->addValidation("Data producao", new TRequiredValidator()); 

        $id->setEditable(false);
        $data_producao->setDatabaseMask('yyyy-mm-dd');
        $producao_detalhe_producao_produto_id->setMinLength(3);
        $producao_detalhe_producao_qtd->setMaxLength(3);

        $data_producao->setMask('dd/mm/yyyy');
        $producao_detalhe_producao_produto_id->setMask('{id} {descricao} ');

        $id->setSize(100);
        $observacao->setSize('70%');
        $data_producao->setSize(110);
        $producao_detalhe_producao_qtd->setSize('70%');
        $producao_detalhe_producao_produto_id->setSize('70%');

        $row1 = $this->form->addFields([new TLabel("Id:", null, '14px', null)],[$id]);
        $row2 = $this->form->addFields([new TLabel("Data produção:", '#ff0000', '14px', null)],[$data_producao]);
        $row3 = $this->form->addFields([new TLabel("Observação:", null, '14px', null)],[$observacao]);
        $row4 = $this->form->addFields([new TFormSeparator("Produtos prontos", '#333333', '18', '#eeeeee')]);
        $row4->layout = [' col-sm-12'];

        $row5 = $this->form->addFields([new TLabel("Produto:", '#ff0000', '14px', null)],[$producao_detalhe_producao_produto_id],[new TLabel("Qtd:", '#ff0000', '14px', null)],[$producao_detalhe_producao_qtd]);
        $row6 = $this->form->addFields([$producao_detalhe_producao_id]);         
        $add_producao_detalhe_producao = new TButton('add_producao_detalhe_producao');

        $action_producao_detalhe_producao = new TAction(array($this, 'onAddProducaoDetalheProducao'));

        $add_producao_detalhe_producao->setAction($action_producao_detalhe_producao, "Adicionar");
        $add_producao_detalhe_producao->setImage('fa:plus #000000');

        $this->form->addFields([$add_producao_detalhe_producao]);

        $this->producao_detalhe_producao_list = new BootstrapDatagridWrapper(new TQuickGrid);
        $this->producao_detalhe_producao_list->style = 'width:100%';
        $this->producao_detalhe_producao_list->class .= ' table-bordered';
        $this->producao_detalhe_producao_list->disableDefaultClick();
        $this->producao_detalhe_producao_list->addQuickColumn('', 'edit', 'left', 50);
        $this->producao_detalhe_producao_list->addQuickColumn('', 'delete', 'left', 50);

        $column_producao_detalhe_producao_produto_id = $this->producao_detalhe_producao_list->addQuickColumn("Produto", 'producao_detalhe_producao_produto_id', 'left');
        $column_producao_detalhe_producao_qtd = $this->producao_detalhe_producao_list->addQuickColumn("Qtd", 'producao_detalhe_producao_qtd', 'left');

        $column_producao_detalhe_producao_qtd->setTotalFunction( function($values) { 
            return array_sum((array) $values); 
        }); 

        $this->producao_detalhe_producao_list->createModel();
        $this->form->addContent([$this->producao_detalhe_producao_list]);

        // create the form actions
        $btn_onsave = $this->form->addAction("Salvar", new TAction([$this, 'onSave']), 'fa:floppy-o #ffffff');
        $btn_onsave->addStyleClass('btn-primary'); 

        $btn_onclear = $this->form->addAction("Limpar formulário", new TAction([$this, 'onClear']), 'fa:eraser #dd5a43');

        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->class = 'form-container';
        $container->add(TBreadCrumb::create(["Movimentações","Lançamento de produção"]));
        $container->add($this->form);

        parent::add($container);

    }

    public function onSave($param = null) 
    {
        try
        {
            TTransaction::open(self::$database); // open a transaction

            /**
            // Enable Debug logger for SQL operations inside the transaction
            TTransaction::setLogger(new TLoggerSTD); // standard output
            TTransaction::setLogger(new TLoggerTXT('log.txt')); // file
            **/

            $messageAction = null;

            $this->form->validate(); // validate form data

            $object = new Producao(); // create an empty object 

            $data = $this->form->getData(); // get form data as array
            $object->fromArray( (array) $data); // load the object with data

            $object->store(); // save the object 

            $messageAction = new TAction(['ProducaoList', 'onShow']);   

            $producao_detalhe_producao_items = $this->storeItems('ProducaoDetalhe', 'producao_id', $object, 'producao_detalhe_producao', function($masterObject, $detailObject){ 

                //code here

            }); 

            // get the generated {PRIMARY_KEY}
            $data->id = $object->id; 

            $this->form->setData($data); // fill form data
            TTransaction::close(); // close the transaction

            /**
            // To define an action to be executed on the message close event:
            $messageAction = new TAction(['className', 'methodName']);
            **/

            new TMessage('info', AdiantiCoreTranslator::translate('Record saved'), $messageAction);

        }
        catch (Exception $e) // in case of exception
        {
            //</catchAutoCode> 

            new TMessage('error', $e->getMessage()); // shows the exception error message
            $this->form->setData( $this->form->getData() ); // keep form data
            TTransaction::rollback(); // undo all pending operations
        }
    }

    public function onEdit( $param )
    {
        try
        {
            if (isset($param['key']))
            {
                $key = $param['key'];  // get the parameter $key
                TTransaction::open(self::$database); // open a transaction

                $object = new Producao($key); // instantiates the Active Record 

                $producao_detalhe_producao_items = $this->loadItems('ProducaoDetalhe', 'producao_id', $object, 'producao_detalhe_producao', function($masterObject, $detailObject, $objectItems){ 

                    //code here

                }); 

                $this->form->setData($object); // fill the form 

                    $this->onReload();

                TTransaction::close(); // close the transaction 
            }
            else
            {
                $this->form->clear();
            }
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
        }
    }

    /**
     * Clear form data
     * @param $param Request
     */
    public function onClear( $param )
    {
        $this->form->clear(true);

        TSession::setValue('producao_detalhe_producao_items', null);

        $this->onReload();
    }

    public function onAddProducaoDetalheProducao( $param )
    {
        try
        {
            $data = $this->form->getData();

            if(!$data->producao_detalhe_producao_produto_id)
            {
                throw new Exception(AdiantiCoreTranslator::translate('The field ^1 is required', "Produto"));
            }             
            if(!$data->producao_detalhe_producao_qtd)
            {
                throw new Exception(AdiantiCoreTranslator::translate('The field ^1 is required', "Qtd"));
            }             

            $producao_detalhe_producao_items = TSession::getValue('producao_detalhe_producao_items');
            $key = isset($data->producao_detalhe_producao_id) && $data->producao_detalhe_producao_id ? $data->producao_detalhe_producao_id : uniqid();
            $fields = []; 

            $fields['producao_detalhe_producao_produto_id'] = $data->producao_detalhe_producao_produto_id;
            $fields['producao_detalhe_producao_qtd'] = $data->producao_detalhe_producao_qtd;
            $producao_detalhe_producao_items[ $key ] = $fields;

            TSession::setValue('producao_detalhe_producao_items', $producao_detalhe_producao_items);

            $data->producao_detalhe_producao_id = '';
            $data->producao_detalhe_producao_produto_id = '';
            $data->producao_detalhe_producao_qtd = '';

            $this->form->setData($data);

            $this->onReload( $param );
        }
        catch (Exception $e)
        {
            $this->form->setData( $this->form->getData());

            new TMessage('error', $e->getMessage());
        }
    }

    public function onEditProducaoDetalheProducao( $param )
    {
        $data = $this->form->getData();

        // read session items
        $items = TSession::getValue('producao_detalhe_producao_items');

        // get the session item
        $item = $items[$param['producao_detalhe_producao_id_row_id']];

        $data->producao_detalhe_producao_produto_id = $item['producao_detalhe_producao_produto_id'];
        $data->producao_detalhe_producao_qtd = $item['producao_detalhe_producao_qtd'];

        $data->producao_detalhe_producao_id = $param['producao_detalhe_producao_id_row_id'];

        // fill product fields
        $this->form->setData( $data );

        $this->onReload( $param );

    }

    public function onDeleteProducaoDetalheProducao( $param )
    {
        $data = $this->form->getData();

        $data->producao_detalhe_producao_produto_id = '';
        $data->producao_detalhe_producao_qtd = '';

        // clear form data
        $this->form->setData( $data );

        // read session items
        $items = TSession::getValue('producao_detalhe_producao_items');

        // delete the item from session
        unset($items[$param['producao_detalhe_producao_id_row_id']]);
        TSession::setValue('producao_detalhe_producao_items', $items);

        // reload sale items
        $this->onReload( $param );

    }

    public function onReloadProducaoDetalheProducao( $param )
    {
        $items = TSession::getValue('producao_detalhe_producao_items'); 

        $this->producao_detalhe_producao_list->clear(); 

        if($items) 
        { 
            $cont = 1; 
            foreach ($items as $key => $item) 
            {
                $rowItem = new StdClass;

                $action_del = new TAction(array($this, 'onDeleteProducaoDetalheProducao')); 
                $action_del->setParameter('producao_detalhe_producao_id_row_id', $key);
                $action_del->setParameter('row_data', base64_encode(serialize($item)));
                $action_del->setParameter('key', $key);

                $action_edi = new TAction(array($this, 'onEditProducaoDetalheProducao'));  
                $action_edi->setParameter('producao_detalhe_producao_id_row_id', $key);  
                $action_edi->setParameter('row_data', base64_encode(serialize($item)));
                $action_edi->setParameter('key', $key);

                $button_del = new TButton('delete_producao_detalhe_producao'.$cont);
                $button_del->setAction($action_del, '');
                $button_del->setFormName($this->form->getName());
                $button_del->class = 'btn btn-link btn-sm';
                $button_del->title = "Excluir";
                $button_del->setImage('fa:trash-o #dd5a43');

                $rowItem->delete = $button_del;

                $button_edi = new TButton('edit_producao_detalhe_producao'.$cont);
                $button_edi->setAction($action_edi, '');
                $button_edi->setFormName($this->form->getName());
                $button_edi->class = 'btn btn-link btn-sm';
                $button_edi->title = "Editar";
                $button_edi->setImage('fa:pencil-square-o #478fca');

                $rowItem->edit = $button_edi;

                $rowItem->producao_detalhe_producao_produto_id = '';
                if(isset($item['producao_detalhe_producao_produto_id']) && $item['producao_detalhe_producao_produto_id'])
                {
                    TTransaction::open('entrega');
                    $produto = Produto::find($item['producao_detalhe_producao_produto_id']);
                    if($produto)
                    {
                        $rowItem->producao_detalhe_producao_produto_id = $produto->render('{id} {descricao} ');
                    }
                    TTransaction::close();
                }

                $rowItem->producao_detalhe_producao_qtd = isset($item['producao_detalhe_producao_qtd']) ? $item['producao_detalhe_producao_qtd'] : '';

                $row = $this->producao_detalhe_producao_list->addItem($rowItem);

                $cont++;
            } 
        } 
    } 

    public function onShow($param = null)
    {
        TSession::setValue('producao_detalhe_producao_items', null);

        $this->onReload();

    } 

    public function onReload($params = null)
    {
        $this->loaded = TRUE;

        $this->onReloadProducaoDetalheProducao($params);
    }

    public function show() 
    { 
        if (!$this->loaded AND (!isset($_GET['method']) OR $_GET['method'] !== 'onReload') ) 
        { 
            $this->onReload( func_get_arg(0) );
        }
        parent::show();
    }

}

