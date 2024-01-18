<?php

class Producao extends TRecord
{
    const TABLENAME  = 'producao';
    const PRIMARYKEY = 'id';
    const IDPOLICY   =  'serial'; // {max, serial}

    private $alterado_por;
    private $criado_por;

    

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('data_producao');
        parent::addAttribute('observacao');
        parent::addAttribute('criado_em');
        parent::addAttribute('criado_por_id');
        parent::addAttribute('alterado_em');
        parent::addAttribute('alterado_por_id');
            
    }

    /**
     * Method set_system_users
     * Sample of usage: $var->system_users = $object;
     * @param $object Instance of SystemUsers
     */
    public function set_alterado_por(SystemUsers $object)
    {
        $this->alterado_por = $object;
        $this->alterado_por_id = $object->id;
    }

    /**
     * Method get_alterado_por
     * Sample of usage: $var->alterado_por->attribute;
     * @returns SystemUsers instance
     */
    public function get_alterado_por()
    {
        TTransaction::open('permission');
        // loads the associated object
        if (empty($this->alterado_por))
            $this->alterado_por = new SystemUsers($this->alterado_por_id);
        TTransaction::close();
        // returns the associated object
        return $this->alterado_por;
    }
    /**
     * Method set_system_users
     * Sample of usage: $var->system_users = $object;
     * @param $object Instance of SystemUsers
     */
    public function set_criado_por(SystemUsers $object)
    {
        $this->criado_por = $object;
        $this->criado_por_id = $object->id;
    }

    /**
     * Method get_criado_por
     * Sample of usage: $var->criado_por->attribute;
     * @returns SystemUsers instance
     */
    public function get_criado_por()
    {
        TTransaction::open('permission');
        // loads the associated object
        if (empty($this->criado_por))
            $this->criado_por = new SystemUsers($this->criado_por_id);
        TTransaction::close();
        // returns the associated object
        return $this->criado_por;
    }

    /**
     * Method getProducaoDetalhes
     */
    public function getProducaoDetalhes()
    {
        $criteria = new TCriteria;
        $criteria->add(new TFilter('producao_id', '=', $this->id));
        return ProducaoDetalhe::getObjects( $criteria );
    }

    
}

