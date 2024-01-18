begin; 

CREATE TABLE estoque( 
      id  SERIAL    NOT NULL  , 
      numero_formula integer   , 
      cliente varchar  (240)   , 
      data_emissao date   , 
      previsao_entrega date   , 
      valor float   , 
      situacao_id integer   NOT NULL  , 
 PRIMARY KEY (id)); 

 CREATE TABLE farmacia( 
      id  SERIAL    NOT NULL  , 
      descricao varchar  (200)   NOT NULL  , 
 PRIMARY KEY (id)); 

 CREATE TABLE forma_pagamento( 
      id  SERIAL    NOT NULL  , 
      descricao char  (20)   NOT NULL  , 
 PRIMARY KEY (id)); 

 CREATE TABLE romaneio( 
      id  SERIAL    NOT NULL  , 
      farmacia_id integer   NOT NULL  , 
      numero_venda char  (10)   , 
      cliente varchar  (200)   , 
      emissao_venda date   , 
      previsao_entrega date   , 
      previsao_entrega_hora time   , 
      valor_venda float   , 
      valor_entrada float   , 
 PRIMARY KEY (id)); 

 CREATE TABLE situacao( 
      id  SERIAL    NOT NULL  , 
      descricao varchar  (100)   , 
 PRIMARY KEY (id)); 

 CREATE TABLE vendendor( 
      id  SERIAL    NOT NULL  , 
      nome varchar  (200)   NOT NULL  , 
 PRIMARY KEY (id)); 

  
 ALTER TABLE estoque ADD CONSTRAINT fk_estoque_1 FOREIGN KEY (situacao_id) references situacao(id); 
ALTER TABLE romaneio ADD CONSTRAINT fk_romaneio_1 FOREIGN KEY (farmacia_id) references farmacia(id); 

  
 
 commit;