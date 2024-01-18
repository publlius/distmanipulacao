<?php

class RomaneioForm extends TWindow
{
    protected $form;
    private $formFields = [];
    private static $database = 'entrega';
    private static $activeRecord = 'Romaneio';
    private static $primaryKey = 'id';
    private static $formName = 'form_Romaneio';

    use Adianti\Base\AdiantiFileSaveTrait;

    /**
     * Form constructor
     * @param $param Request
     */
    public function __construct( $param )
    {
        parent::__construct();
        parent::setSize(400, null);
        parent::setTitle("Cadastro de romaneio");
        parent::setProperty('class', 'window_modal');

        // creates the form
        $this->form = new BootstrapFormBuilder(self::$formName);
        // define the form title
        $this->form->setFormTitle("Cadastro de romaneio");


        $importar = new TFile('importar');

        $importar->setSize('70%');
        $importar->enableFileHandling();
        $importar->setAllowedExtensions(["csv"]);

        $row1 = $this->form->addFields([new TLabel("Romaneio:", null, '14px', null)],[$importar]);

        // create the form actions
        $btn_onimportar = $this->form->addAction("Importar", new TAction([$this, 'onImportar']), 'fa:save #000000');

        parent::add($this->form);
    }

    public function onImportar($param = null) 
    {
        try 
        {
            //code here
            //Obtém o nome do arquivo
            $fileName = json_decode(urldecode($param['importar']))->fileName;

            //Abre o arquivo
            $handle = fopen($fileName, "r");

            //Abre uma transação com o banco de dados
            TTransaction::open(self::$database);

            //Contador de registros inseridos
            $count = 0;

            //Separador das colunas do arquivo CSV
            $separador = ',';

            //Limite de caracteres que uma linha pode ter, 0 = sem limite
            $limite_da_linha = 0;

            //Percorre todas as linhas do arquivos
            while (($dados = fgetcsv($handle, $limite_da_linha, $separador)) !== FALSE)
            {
                //Monta o objeto romaneio com as colunas do arquivo CSV
                $romaneio                        = new Romaneio;
                $romaneio->farmacia_id           = $dados[0];
                $romaneio->numero_venda          = $dados[1];
                $vazia                           = $dados[2];
                $vazia                           = $dados[3];
                $romaneio->cliente               = $dados[4];
                $romaneio->emissao_venda         = $dados[5];
                $romaneio->previsao_entrega      = $dados[6];
                $romaneio->previsao_entrega_hora = $dados[7];
                $romaneio->valor_venda           = $dados[8];
                $romaneio->valor_entrada         = $dados[9];
                $romaneio->valor_saldo           = $dados[10];

                //Insere um novo romaneio
                $romaneio->store();
                $count++;
            }

            //Fecha a transação
            TTransaction::close();

            //Fecha o arquivo
            fclose($handle);

            //Ação a ser executada quando a mensagem de sucesso for fechada
            $closeAction = new TAction(['RomaneioList', 'onReload']);

            //Mensagem de sucesse
            new TMessage('info', "{$count} vendas foram importadas!", $closeAction);

        }
        catch (Exception $e) 
        {
            new TMessage('error', $e->getMessage());    
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

                $object = new Romaneio($key); // instantiates the Active Record 

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

