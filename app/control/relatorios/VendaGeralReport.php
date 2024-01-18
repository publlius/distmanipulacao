<?php

class VendaGeralReport extends TPage
{
    private $form; // form
    private $loaded;
    private static $database = 'entrega';
    private static $activeRecord = 'VendaGeral';
    private static $primaryKey = 'romaneio_id';
    private static $formName = 'formReport_VendaGeral';

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
        $this->form->setFormTitle("Vendas (geral)");

        $farmacia_id = new TDBCombo('farmacia_id', 'permission', 'SystemUnit', 'id', '{name}','name asc'  );
        $vendedor_id = new TDBCombo('vendedor_id', 'permission', 'SystemUsers', 'id', '{name}','name asc'  );
        $catalogo = new TDBCombo('catalogo', 'entrega', 'Venda', 'catalogo', '{catalogo}','catalogo asc'  );
        $forma_pagamento_id = new TDBCombo('forma_pagamento_id', 'entrega', 'FormaPagamento', 'id', '{descricao}','descricao asc'  );
        $data_ini = new TDate('data_ini');
        $data_fim = new TDate('data_fim');

        $data_ini->setDatabaseMask('yyyy-mm-dd');
        $data_fim->setDatabaseMask('yyyy-mm-dd');

        $data_ini->setMask('dd/mm/yyyy');
        $data_fim->setMask('dd/mm/yyyy');

        $data_ini->setSize(110);
        $data_fim->setSize(110);
        $catalogo->setSize('70%');
        $farmacia_id->setSize('70%');
        $vendedor_id->setSize('70%');
        $forma_pagamento_id->setSize('70%');

        $row1 = $this->form->addFields([new TLabel("Filial:", null, '14px', null)],[$farmacia_id]);
        $row2 = $this->form->addFields([new TLabel("Vendedor:", null, '14px', null)],[$vendedor_id]);
        $row3 = $this->form->addFields([new TLabel("Catalogo:", null, '14px', null)],[$catalogo]);
        $row4 = $this->form->addFields([new TLabel("Forma pagamento:", null, '14px', null)],[$forma_pagamento_id]);
        $row5 = $this->form->addFields([new TLabel("Data ini:", null, '14px', null)],[$data_ini],[new TLabel("Data fim:", null, '14px', null)],[$data_fim]);

        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue(__CLASS__.'_filter_data') );

        $btn_ongeneratehtml = $this->form->addAction("Gerar HTML", new TAction([$this, 'onGenerateHtml']), 'fas:code #ffffff');
        $btn_ongeneratehtml->addStyleClass('btn-primary'); 

        $btn_ongeneratepdf = $this->form->addAction("Gerar PDF", new TAction([$this, 'onGeneratePdf']), 'far:file-pdf #d44734');

        $btn_ongeneratexls = $this->form->addAction("Gerar XLS", new TAction([$this, 'onGenerateXls']), 'far:file-excel #00a65a');

        $btn_ongeneratertf = $this->form->addAction("Gerar RTF", new TAction([$this, 'onGenerateRtf']), 'far:file-alt #324bcc');

        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->class = 'form-container';
        $container->add(TBreadCrumb::create(["Relatórios","Vendas (geral)"]));
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

        if (isset($data->farmacia_id) AND ( (is_scalar($data->farmacia_id) AND $data->farmacia_id !== '') OR (is_array($data->farmacia_id) AND (!empty($data->farmacia_id)) )) )
        {

            $filters[] = new TFilter('farmacia_id', '=', $data->farmacia_id);// create the filter 
        }
        if (isset($data->vendedor_id) AND ( (is_scalar($data->vendedor_id) AND $data->vendedor_id !== '') OR (is_array($data->vendedor_id) AND (!empty($data->vendedor_id)) )) )
        {

            $filters[] = new TFilter('vendedor_id', '=', $data->vendedor_id);// create the filter 
        }
        if (isset($data->catalogo) AND ( (is_scalar($data->catalogo) AND $data->catalogo !== '') OR (is_array($data->catalogo) AND (!empty($data->catalogo)) )) )
        {

            $filters[] = new TFilter('catalogo', 'like', "%{$data->catalogo}%");// create the filter 
        }
        if (isset($data->forma_pagamento_id) AND ( (is_scalar($data->forma_pagamento_id) AND $data->forma_pagamento_id !== '') OR (is_array($data->forma_pagamento_id) AND (!empty($data->forma_pagamento_id)) )) )
        {

            $filters[] = new TFilter('forma_pagamento_id', '=', $data->forma_pagamento_id);// create the filter 
        }
        if (isset($data->data_ini) AND ( (is_scalar($data->data_ini) AND $data->data_ini !== '') OR (is_array($data->data_ini) AND (!empty($data->data_ini)) )) )
        {

            $filters[] = new TFilter('data_ini', '>=', $data->data_ini);// create the filter 
        }
        if (isset($data->data_fim) AND ( (is_scalar($data->data_fim) AND $data->data_fim !== '') OR (is_array($data->data_fim) AND (!empty($data->data_fim)) )) )
        {

            $filters[] = new TFilter('data_fim', '<=', $data->data_fim);// create the filter 
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
            // creates a repository for VendaGeral
            $repository = new TRepository(self::$activeRecord);
            // creates a criteria
            $criteria = new TCriteria;

            $param['order'] = 'vendedor';
            $param['direction'] = 'asc';

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
                $widths = array(200,200,200,200,200,200,200,200);

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
                    $tr->addCell("Filial", 'left', 'title');
                    $tr->addCell("Vendedor", 'left', 'title');
                    $tr->addCell("Catalogo", 'left', 'title');
                    $tr->addCell("Cf", 'left', 'title');
                    $tr->addCell("Pagamento", 'left', 'title');
                    $tr->addCell("R$ Fórmula", 'right', 'title');
                    $tr->addCell("Valor venda", 'right', 'title');
                    $tr->addCell("% Desconto", 'right', 'title');

                    $grandTotal = [];
                    $breakTotal = [];
                    $breakValue = null;
                    $firstRow = true;

                    // controls the background filling
                    $colour = false;                
                    foreach ($objects as $object)
                    {
                        $style = $colour ? 'datap' : 'datai';

                        $column_calculated_1 = $object->evaluate('=( ({valor_formula} - {valor_venda} ) / ({valor_formula}) *100  )');

                        if ($object->vendedor !== $breakValue)
                        {
                            if (!$firstRow)
                            {
                                $tr->addRow();

                                $breakTotal_valor_formula = array_sum($breakTotal['valor_formula']);
                                $breakTotal_valor_venda = array_sum($breakTotal['valor_venda']);
                                $breakTotal_column_calculated_1 = array_sum($breakTotal['column_calculated_1']) / count($breakTotal['column_calculated_1']);

                                $breakTotal_valor_formula = call_user_func(function($value)
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
                                }, $breakTotal_valor_formula); 

                                $breakTotal_valor_venda = call_user_func(function($value)
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
                                }, $breakTotal_valor_venda); 

                                $tr->addCell('', 'center', 'breakTotal');
                                $tr->addCell('', 'center', 'breakTotal');
                                $tr->addCell('', 'center', 'breakTotal');
                                $tr->addCell('', 'center', 'breakTotal');
                                $tr->addCell('', 'center', 'breakTotal');
                                $tr->addCell($breakTotal_valor_formula, 'right', 'breakTotal');
                                $tr->addCell($breakTotal_valor_venda, 'right', 'breakTotal');
                                $tr->addCell($breakTotal_column_calculated_1, 'right', 'breakTotal');
                            }
                            $tr->addRow();
                            $tr->addCell($object->render('{vendedor}'), 'left', 'break', 8);
                            $breakTotal = [];
                        }
                        $breakValue = $object->vendedor;

                        $grandTotal['valor_formula'][] = $object->valor_formula;
                        $breakTotal['valor_formula'][] = $object->valor_formula;
                        $grandTotal['valor_venda'][] = $object->valor_venda;
                        $breakTotal['valor_venda'][] = $object->valor_venda;
                        $grandTotal['column_calculated_1'][] = $column_calculated_1;
                        $breakTotal['column_calculated_1'][] = $column_calculated_1;

                        $firstRow = false;

                        $object->valor_formula = call_user_func(function($value, $object, $row) 
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
                        }, $object->valor_formula, $object, null);

                        $object->valor_venda = call_user_func(function($value, $object, $row) 
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
                        }, $object->valor_venda, $object, null);

                        $tr->addRow();

                        $tr->addCell($object->filial, 'left', $style);
                        $tr->addCell($object->vendedor, 'left', $style);
                        $tr->addCell($object->catalogo, 'left', $style);
                        $tr->addCell($object->cf, 'left', $style);
                        $tr->addCell($object->forma_pagamento, 'left', $style);
                        $tr->addCell($object->valor_formula, 'right', $style);
                        $tr->addCell($object->valor_venda, 'right', $style);
                        $tr->addCell($column_calculated_1, 'right', $style);

                        $colour = !$colour;
                    }

                    $tr->addRow();

                    $breakTotal_valor_formula = array_sum($breakTotal['valor_formula']);
                    $breakTotal_valor_venda = array_sum($breakTotal['valor_venda']);
                    $breakTotal_column_calculated_1 = array_sum($breakTotal['column_calculated_1']) / count($breakTotal['column_calculated_1']);

                    $breakTotal_valor_formula = call_user_func(function($value)
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
                    }, $breakTotal_valor_formula); 

                    $breakTotal_valor_venda = call_user_func(function($value)
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
                    }, $breakTotal_valor_venda); 

                    $tr->addCell('', 'center', 'breakTotal');
                    $tr->addCell('', 'center', 'breakTotal');
                    $tr->addCell('', 'center', 'breakTotal');
                    $tr->addCell('', 'center', 'breakTotal');
                    $tr->addCell('', 'center', 'breakTotal');
                    $tr->addCell($breakTotal_valor_formula, 'right', 'breakTotal');
                    $tr->addCell($breakTotal_valor_venda, 'right', 'breakTotal');
                    $tr->addCell($breakTotal_column_calculated_1, 'right', 'breakTotal');

                    $tr->addRow();

                    $grandTotal_valor_formula = array_sum($grandTotal['valor_formula']);
                    $grandTotal_valor_venda = array_sum($grandTotal['valor_venda']);
                    $grandTotal_column_calculated_1 = array_sum($grandTotal['column_calculated_1']) / count($grandTotal['column_calculated_1']);

                    $grandTotal_valor_formula = call_user_func(function($value)
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
                    }, $grandTotal_valor_formula); 

                    $grandTotal_valor_venda = call_user_func(function($value)
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
                    }, $grandTotal_valor_venda); 

                    $tr->addCell('', 'center', 'total');
                    $tr->addCell('', 'center', 'total');
                    $tr->addCell('', 'center', 'total');
                    $tr->addCell('', 'center', 'total');
                    $tr->addCell('', 'center', 'total');
                    $tr->addCell($grandTotal_valor_formula, 'right', 'total');
                    $tr->addCell($grandTotal_valor_venda, 'right', 'total');
                    $tr->addCell($grandTotal_column_calculated_1, 'right', 'total');

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

