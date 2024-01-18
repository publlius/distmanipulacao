<?php

class EstoqueForm extends TPage
{
    protected $form;
    private $formFields = [];
    private static $database = 'entrega';
    private static $activeRecord = 'Estoque';
    private static $primaryKey = 'id';
    private static $formName = 'form_Estoque';

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
        $this->form->setFormTitle("Cadastro de estoque");


        $id = new TEntry('id');
        $numero_formula = new TEntry('numero_formula');
        $cliente = new TEntry('cliente');
        $data_emissao = new TDate('data_emissao');
        $previsao_entrega = new TDate('previsao_entrega');
        $valor = new TNumeric('valor', '2', ',', '.' );
        $situacao_id = new TDBCombo('situacao_id', 'entrega', 'Situacao', 'id', '{id}','id asc'  );

        $situacao_id->addValidation("Situacao id", new TRequiredValidator()); 

        $id->setEditable(false);

        $data_emissao->setMask('dd/mm/yyyy');
        $previsao_entrega->setMask('dd/mm/yyyy');

        $data_emissao->setDatabaseMask('yyyy-mm-dd');
        $previsao_entrega->setDatabaseMask('yyyy-mm-dd');

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
        $row7 = $this->form->addFields([new TLabel("Situacao id:", '#ff0000', '14px', null)],[$situacao_id]);

        // create the form actions
        $btn_onsave = $this->form->addAction("Salvar", new TAction([$this, 'onSave']), 'fa:floppy-o #ffffff');
        $btn_onsave->addStyleClass('btn-primary'); 

        $btn_onclear = $this->form->addAction("Limpar formulário", new TAction([$this, 'onClear']), 'fa:eraser #dd5a43');

        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->class = 'form-container';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
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

            $object = new Estoque(); // create an empty object 

            $data = $this->form->getData(); // get form data as array
            $object->fromArray( (array) $data); // load the object with data

            $object->store(); // save the object 

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

                $object = new Estoque($key); // instantiates the Active Record 

                $this->form->setData($object); // fill the form 

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

    }

    public function onShow($param = null)
    {

    } 

}

