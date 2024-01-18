<?php

class ProducaoDetalhe extends TRecord
{
    const TABLENAME  = 'producao_detalhe';
    const PRIMARYKEY = 'id';
    const IDPOLICY   =  'serial'; // {max, serial}

    private $producao;
    private $alterado_por;
    private $criado_por;
    private $produto;

    

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('producao_id');
        parent::addAttribute('produto_id');
        parent::addAttribute('qtd');
        parent::addAttribute('criado_em');
        parent::addAttribute('criado_por_id');
        parent::addAttribute('alterado_em');
        parent::addAttribute('alterado_por_id');
            
    }

    /**
     * Method set_producao
     * Sample of usage: $var->producao = $object;
     * @param $object Instance of Producao
     */
    public function set_producao(Producao $object)
    {
        $this->producao = $object;
        $this->producao_id = $object->id;
    }

    /**
     * Method get_producao
     * Sample of usage: $var->producao->attribute;
     * @returns Producao instance
     */
    public function get_producao()
    {
    
        // loads the associated object
        if (empty($this->producao))
            $this->producao = new Producao($this->producao_id);
    
        // returns the associated object
        return $this->producao;
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
     * Method set_produto
     * Sample of usage: $var->produto = $object;
     * @param $object Instance of Produto
     */
    public function set_produto(Produto $object)
    {
        $this->produto = $object;
        $this->produto_id = $object->id;
    }

    /**
     * Method get_produto
     * Sample of usage: $var->produto->attribute;
     * @returns Produto instance
     */
    public function get_produto()
    {
    
        // loads the associated object
        if (empty($this->produto))
            $this->produto = new Produto($this->produto_id);
    
        // returns the associated object
        return $this->produto;
    }

    
}

