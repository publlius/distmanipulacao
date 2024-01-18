<?php

class VendaReport extends TPage
{
    private $form; // form
    private $loaded;
    private static $database = 'entrega';
    private static $activeRecord = 'Venda';
    private static $primaryKey = 'id';
    private static $formName = 'formReport_Venda';

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
        $this->form->setFormTitle("Estoque");

        $id = new TEntry('id');
        $romaneio_id = new TDBCombo('romaneio_id', 'entrega', 'Romaneio', 'id', '{numero_venda} - {cliente} ','id asc'  );
        $cf = new TEntry('cf');
        $data_venda = new TDate('data_venda');

        $data_venda->setDatabaseMask('yyyy-mm-dd');
        $data_venda->setMask('dd/mm/yyyy');

        $id->setSize(100);
        $cf->setSize('70%');
        $data_venda->setSize(110);
        $romaneio_id->setSize('70%');

        $row1 = $this->form->addFields([new TLabel("Id:", null, '14px', null)],[$id]);
        $row2 = $this->form->addFields([new TLabel("Romaneio:", null, '14px', null)],[$romaneio_id]);
        $row3 = $this->form->addFields([new TLabel("Cf:", null, '14px', null)],[$cf]);
        $row4 = $this->form->addFields([new TLabel("Data venda:", null, '14px', null)],[$data_venda]);

        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue(__CLASS__.'_filter_data') );

        $btn_ongeneratehtml = $this->form->addAction("Gerar HTML", new TAction([$this, 'onGenerateHtml']), 'fa:code #ffffff');
        $btn_ongeneratehtml->addStyleClass('btn-primary'); 

        $btn_ongeneratepdf = $this->form->addAction("Gerar PDF", new TAction([$this, 'onGeneratePdf']), 'fa:file-pdf-o #d44734');

        $btn_ongeneratexls = $this->form->addAction("Gerar XLS", new TAction([$this, 'onGenerateXls']), 'fa:file-excel-o #00a65a');

        $btn_ongeneratertf = $this->form->addAction("Gerar RTF", new TAction([$this, 'onGenerateRtf']), 'fa:file-text-o #324bcc');

        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->class = 'form-container';
        $container->add(TBreadCrumb::create(["Relatórios","Estoque"]));
        $container->add($this->form);

        parent::add($container);

    }

    public function onGenerateHtml($param = null) 
    {
        $this->onGenerate('html');
    }

    public function onGeneratePdf($param = null) 
    {
        $this->onGenerate('pdf');
    }

    public function onGenerateXls($param = null) 
    {
        $this->onGenerate('xls');
    }

    public function onGenerateRtf($param = null) 
    {
        $this->onGenerate('rtf');
    }

    /**
     * Register the filter in the session
     */
    public function getFilters()
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
        if (isset($data->romaneio_id) AND ( (is_scalar($data->romaneio_id) AND $data->romaneio_id !== '') OR (is_array($data->romaneio_id) AND (!empty($data->romaneio_id)) )) )
        {

            $filters[] = new TFilter('romaneio_id', '=', $data->romaneio_id);// create the filter 
        }
        if (isset($data->cf) AND ( (is_scalar($data->cf) AND $data->cf !== '') OR (is_array($data->cf) AND (!empty($data->cf)) )) )
        {

            $filters[] = new TFilter('cf', '=', $data->cf);// create the filter 
        }
        if (isset($data->data_venda) AND ( (is_scalar($data->data_venda) AND $data->data_venda !== '') OR (is_array($data->data_venda) AND (!empty($data->data_venda)) )) )
        {

            $filters[] = new TFilter('data_venda', '=', $data->data_venda);// create the filter 
        }

        // fill the form with data again
        $this->form->setData($data);

        // keep the search data in the session
        TSession::setValue(__CLASS__.'_filter_data', $data);

        return $filters;
    }

    public function onGenerate($format)
    {
        try
        {
            $filters = $this->getFilters();
            // open a transaction with database 'entrega'
            TTransaction::open(self::$database);
            $param = [];
            // creates a repository for Venda
            $repository = new TRepository(self::$activeRecord);
            // creates a criteria
            $criteria = new TCriteria;

            $criteria->setProperties($param);

            if ($filters)
            {
                foreach ($filters as $filter) 
                {
                    $criteria->add($filter);       
                }
            }

            // load the objects according to criteria
            $objects = $repository->load($criteria, FALSE);

            if ($objects)
            {
                $widths = array(200,200,200,200,200,200);

                switch ($format)
                {
                    case 'html':
                        $tr = new TTableWriterHTML($widths);
                        break;
                    case 'xls':
                        $tr = new TTableWriterXLS($widths);
                        break;
                    case 'pdf':
                        $tr = new TTableWriterPDF($widths, 'L');
                        break;
                    case 'rtf':
                        if (!class_exists('PHPRtfLite_Autoloader'))
                        {
                            PHPRtfLite::registerAutoloader();
                        }
                        $tr = new TTableWriterRTF($widths, 'L');
                        break;
                }

                if (!empty($tr))
                {
                    // create the document styles
                    $tr->addStyle('title', 'Helvetica', '10', 'B',   '#000000', '#dbdbdb');
                    $tr->addStyle('datap', 'Arial', '10', '',    '#333333', '#f0f0f0');
                    $tr->addStyle('datai', 'Arial', '10', '',    '#333333', '#ffffff');
                    $tr->addStyle('header', 'Helvetica', '16', 'B',   '#5a5a5a', '#6B6B6B');
                    $tr->addStyle('footer', 'Helvetica', '10', 'B',  '#5a5a5a', '#A3A3A3');
                    $tr->addStyle('break', 'Helvetica', '10', 'B',  '#ffffff', '#9a9a9a');
                    $tr->addStyle('total', 'Helvetica', '10', 'I',  '#000000', '#c7c7c7');
                    $tr->addStyle('breakTotal', 'Helvetica', '10', 'I',  '#000000', '#c6c8d0');

                    // add titles row
                    $tr->addRow();
                    $tr->addCell("Romaneio", 'left', 'title');
                    $tr->addCell("Cliente", 'left', 'title');
                    $tr->addCell("Vendido por", 'left', 'title');
                    $tr->addCell("Entregue por", 'left', 'title');
                    $tr->addCell("Cf", 'left', 'title');
                    $tr->addCell("Data venda", 'left', 'title');

                    $grandTotal = [];
                    $breakTotal = [];
                    $breakValue = null;
                    $firstRow = true;

                    // controls the background filling
                    $colour = false;                
                    foreach ($objects as $object)
                    {
                        $style = $colour ? 'datap' : 'datai';

                        $firstRow = false;

                        $tr->addRow();

                        $tr->addCell($object->romaneio->numero_venda, 'left', $style);
                        $tr->addCell($object->romaneio->cliente, 'left', $style);
                        $tr->addCell($object->vendido_por->name, 'left', $style);
                        $tr->addCell($object->entregue_por->name, 'left', $style);
                        $tr->addCell($object->cf, 'left', $style);
                        $tr->addCell($object->data_venda, 'left', $style);

                        $colour = !$colour;
                    }

                    $file = 'report_'.uniqid().".{$format}";
                    // stores the file
                    if (!file_exists("app/output/{$file}") || is_writable("app/output/{$file}"))
                    {
                        $tr->save("app/output/{$file}");
                    }
                    else
                    {
                        throw new Exception(_t('Permission denied') . ': ' . "app/output/{$file}");
                    }

                    parent::openFile("app/output/{$file}");

                    // shows the success message
                    new TMessage('info', _t('Report generated. Please, enable popups'));
                }
            }
            else
            {
                new TMessage('error', _t('No records found'));
            }

            // close the transaction
            TTransaction::close();
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

}

