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
        parent::setTitle("Importação de romaneio");
        parent::setProperty('class', 'window_modal');

        // creates the form
        $this->form = new BootstrapFormBuilder(self::$formName);
        // define the form title
        $this->form->setFormTitle("Importação de romaneio");


        $importar = new TFile('importar');

        $importar->setSize('70%');
        $importar->enableFileHandling();
        $importar->setAllowedExtensions(["csv"]);

        $row1 = $this->form->addFields([new TLabel("Romaneio por farmácia:", null, '14px', null)],[$importar]);

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
            $separador = ';';

            //Limite de caracteres que uma linha pode ter, 0 = sem limite
            $limite_da_linha = 0;

            //Percorre todas as linhas do arquivos
            while (($dados = fgetcsv($handle, $limite_da_linha, $separador)) !== FALSE)
            {
                //Monta o objeto romaneio com as colunas do arquivo CSV
                $romaneio                        = new Romaneio;
                $romaneio->farmacia_id           = $dados[0];
                $vazia                           = $dados[1];
                $romaneio->numero_venda          = $dados[2];
                $vazia                           = $dados[3];
                $vazia                           = $dados[4];
                $romaneio->cliente               = $dados[5];
                $vazia                           = $dados[6];
                $vazia                           = $dados[7];
                $vazia                           = $dados[8];
                $vazia                           = $dados[9];
                $vazia                           = $dados[10];
                $vazia                           = $dados[11];
                $romaneio->emissao_venda         = TDate::date2us($dados[12]);
                $vazia                           = $dados[13];
                $vazia                           = $dados[14];
                $vazia                           = $dados[15];
                $vazia                           = $dados[16];
                $romaneio->previsao_entrega      = TDate::date2us($dados[17]);
                $vazia                           = $dados[18];
                $romaneio->previsao_entrega_hora = $dados[19];
                $vazia                           = $dados[20];
                $vazia                           = $dados[21];
                
                //$romaneio->valor_venda           = TNumeric::(($dados[20]), '2', ' ,', '.' );
                //$object = $this->form->getData('Salario');
                //$romaneio->valor_venda = str_replace('.',' ', ($dados[20])->valor_venda);
                $romaneio->valor_venda = str_replace(',','.', $dados[22]);            
                $romaneio->valor_entrada         = $dados[23];
                $romaneio->valor_saldo           = $dados[24];
                // $vazia                           = $dados[17];

                //Insere um novo romaneio
            //if($romaneio->store() > 6)
            //{
            //    $romaneio->store();
            //}                
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

