begin; 

CREATE TABLE estoque( 
      id number(10)    NOT NULL , 
      numero_formula number(10)  (10)   , 
      cliente varchar  (240)   , 
      data_emissao date   , 
      previsao_entrega date   , 
      valor binary_double  (10,2)   , 
      situacao_id number(10)    NOT NULL , 
 PRIMARY KEY (id)); 

 CREATE TABLE farmacia( 
      id number(10)    NOT NULL , 
      descricao varchar  (200)    NOT NULL , 
 PRIMARY KEY (id)); 

 CREATE TABLE forma_pagamento( 
      id number(10)    NOT NULL , 
      descricao char  (20)    NOT NULL , 
 PRIMARY KEY (id)); 

 CREATE TABLE romaneio( 
      id number(10)    NOT NULL , 
      farmacia_id number(10)    NOT NULL , 
      numero_venda char  (10)   , 
      cliente varchar  (200)   , 
      emissao_venda date   , 
      previsao_entrega date   , 
      previsao_entrega_hora time   , 
      valor_venda binary_double  (10,2)   , 
      valor_entrada binary_double  (10,2)   , 
 PRIMARY KEY (id)); 

 CREATE TABLE situacao( 
      id number(10)    NOT NULL , 
      descricao varchar  (100)   , 
 PRIMARY KEY (id)); 

 CREATE TABLE vendendor( 
      id number(10)    NOT NULL , 
      nome varchar  (200)    NOT NULL , 
 PRIMARY KEY (id)); 

  
 ALTER TABLE estoque ADD CONSTRAINT fk_estoque_1 FOREIGN KEY (situacao_id) references situacao(id); 
ALTER TABLE romaneio ADD CONSTRAINT fk_romaneio_1 FOREIGN KEY (farmacia_id) references farmacia(id); 
 CREATE SEQUENCE estoque_id_seq START WITH 1 INCREMENT BY 1; 

CREATE OR REPLACE TRIGGER estoque_id_seq_tr 

BEFORE INSERT ON estoque FOR EACH ROW 

WHEN 

(NEW.id IS NULL) 

BEGIN 

SELECT estoque_id_seq.NEXTVAL INTO :NEW.id FROM DUAL; 

END;
CREATE SEQUENCE farmacia_id_seq START WITH 1 INCREMENT BY 1; 

CREATE OR REPLACE TRIGGER farmacia_id_seq_tr 

BEFORE INSERT ON farmacia FOR EACH ROW 

WHEN 

(NEW.id IS NULL) 

BEGIN 

SELECT farmacia_id_seq.NEXTVAL INTO :NEW.id FROM DUAL; 

END;
CREATE SEQUENCE forma_pagamento_id_seq START WITH 1 INCREMENT BY 1; 

CREATE OR REPLACE TRIGGER forma_pagamento_id_seq_tr 

BEFORE INSERT ON forma_pagamento FOR EACH ROW 

WHEN 

(NEW.id IS NULL) 

BEGIN 

SELECT forma_pagamento_id_seq.NEXTVAL INTO :NEW.id FROM DUAL; 

END;
CREATE SEQUENCE romaneio_id_seq START WITH 1 INCREMENT BY 1; 

CREATE OR REPLACE TRIGGER romaneio_id_seq_tr 

BEFORE INSERT ON romaneio FOR EACH ROW 

WHEN 

(NEW.id IS NULL) 

BEGIN 

SELECT romaneio_id_seq.NEXTVAL INTO :NEW.id FROM DUAL; 

END;
CREATE SEQUENCE situacao_id_seq START WITH 1 INCREMENT BY 1; 

CREATE OR REPLACE TRIGGER situacao_id_seq_tr 

BEFORE INSERT ON situacao FOR EACH ROW 

WHEN 

(NEW.id IS NULL) 

BEGIN 

SELECT situacao_id_seq.NEXTVAL INTO :NEW.id FROM DUAL; 

END;
CREATE SEQUENCE vendendor_id_seq START WITH 1 INCREMENT BY 1; 

CREATE OR REPLACE TRIGGER vendendor_id_seq_tr 

BEFORE INSERT ON vendendor FOR EACH ROW 

WHEN 

(NEW.id IS NULL) 

BEGIN 

SELECT vendendor_id_seq.NEXTVAL INTO :NEW.id FROM DUAL; 

END;
 
  
 
 commit;