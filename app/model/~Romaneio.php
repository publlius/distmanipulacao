<?php

class Romaneio extends TRecord
{
    const TABLENAME  = 'romaneio';
    const PRIMARYKEY = 'id';
    const IDPOLICY   =  'serial'; // {max, serial}

    private $farmacia;
    private $situacao;

    

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('farmacia_id');
        parent::addAttribute('numero_venda');
        parent::addAttribute('cliente');
        parent::addAttribute('emissao_venda');
        parent::addAttribute('previsao_entrega');
        parent::addAttribute('previsao_entrega_hora');
        parent::addAttribute('valor_venda');
        parent::addAttribute('valor_entrada');
        parent::addAttribute('valor_saldo');
        parent::addAttribute('situacao_id');
            
    }

    /**
     * Method set_system_unit
     * Sample of usage: $var->system_unit = $object;
     * @param $object Instance of SystemUnit
     */
    public function set_farmacia(SystemUnit $object)
    {
        $this->farmacia = $object;
        $this->farmacia_id = $object->id;
    }

    /**
     * Method get_farmacia
     * Sample of usage: $var->farmacia->attribute;
     * @returns SystemUnit instance
     */
    public function get_farmacia()
    {
        TTransaction::open('permission');
        // loads the associated object
        if (empty($this->farmacia))
            $this->farmacia = new SystemUnit($this->farmacia_id);
        TTransaction::close();
        // returns the associated object
        return $this->farmacia;
    }
    /**
     * Method set_situacao
     * Sample of usage: $var->situacao = $object;
     * @param $object Instance of Situacao
     */
    public function set_situacao(Situacao $object)
    {
        $this->situacao = $object;
        $this->situacao_id = $object->id;
    }

    /**
     * Method get_situacao
     * Sample of usage: $var->situacao->attribute;
     * @returns Situacao instance
     */
    public function get_situacao()
    {
    
        // loads the associated object
        if (empty($this->situacao))
            $this->situacao = new Situacao($this->situacao_id);
    
        // returns the associated object
        return $this->situacao;
    }

    /**
     * Method getVendas
     */
    public function getVendas()
    {
        $criteria = new TCriteria;
        $criteria->add(new TFilter('romaneio_id', '=', $this->id));
        return Venda::getObjects( $criteria );
    }

    
}

