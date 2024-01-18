begin; 

CREATE TABLE estoque( 
      id  INT IDENTITY    NOT NULL  , 
      numero_formula int  (10)   , 
      cliente varchar  (240)   , 
      data_emissao date   , 
      previsao_entrega date   , 
      valor float  (10,2)   , 
      situacao_id int   NOT NULL  , 
 PRIMARY KEY (id)); 

 CREATE TABLE farmacia( 
      id  INT IDENTITY    NOT NULL  , 
      descricao varchar  (200)   NOT NULL  , 
 PRIMARY KEY (id)); 

 CREATE TABLE forma_pagamento( 
      id  INT IDENTITY    NOT NULL  , 
      descricao char  (20)   NOT NULL  , 
 PRIMARY KEY (id)); 

 CREATE TABLE romaneio( 
      id  INT IDENTITY    NOT NULL  , 
      farmacia_id int   NOT NULL  , 
      numero_venda char  (10)   , 
      cliente varchar  (200)   , 
      emissao_venda date   , 
      previsao_entrega date   , 
      previsao_entrega_hora time   , 
      valor_venda float  (10,2)   , 
      valor_entrada float  (10,2)   , 
 PRIMARY KEY (id)); 

 CREATE TABLE situacao( 
      id  INT IDENTITY    NOT NULL  , 
      descricao varchar  (100)   , 
 PRIMARY KEY (id)); 

 CREATE TABLE vendendor( 
      id  INT IDENTITY    NOT NULL  , 
      nome varchar  (200)   NOT NULL  , 
 PRIMARY KEY (id)); 

  
 ALTER TABLE estoque ADD CONSTRAINT fk_estoque_1 FOREIGN KEY (situacao_id) references situacao(id); 
ALTER TABLE romaneio ADD CONSTRAINT fk_romaneio_1 FOREIGN KEY (farmacia_id) references farmacia(id); 

  
 
 commit;