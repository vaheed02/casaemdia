-- ServiJá / servicos_app — schema + usuários demo
-- Importe no phpMyAdmin da Hostinger no banco: u240559973_diarista
-- Senha demo de todos os usuários: demo123

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `avaliacoes`;
DROP TABLE IF EXISTS `pagamentos`;
DROP TABLE IF EXISTS `agendamentos`;
DROP TABLE IF EXISTS `enderecos`;
DROP TABLE IF EXISTS `perfis_prestador`;
DROP TABLE IF EXISTS `usuarios`;
DROP TABLE IF EXISTS `migrations`;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE `usuarios` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(120) NOT NULL,
  `email` VARCHAR(180) NOT NULL,
  `senha` VARCHAR(255) NOT NULL,
  `telefone` VARCHAR(20) NULL,
  `role` ENUM('cliente','prestador','admin') NOT NULL DEFAULT 'cliente',
  `ativo` TINYINT(1) NOT NULL DEFAULT 1,
  `reset_token` VARCHAR(64) NULL,
  `token_expira` DATETIME NULL,
  `created_at` DATETIME NULL,
  `updated_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `perfis_prestador` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `usuario_id` INT UNSIGNED NOT NULL,
  `tipos_servico` VARCHAR(180) NOT NULL COMMENT 'CSV: diarista,passeador,telhado,piscinas,jardins,hidraulico',
  `bio` TEXT NULL,
  `valor_diaria` DECIMAL(10,2) NOT NULL DEFAULT 0,
  `valor_passeio` DECIMAL(10,2) NOT NULL DEFAULT 0,
  `valor_telhado` DECIMAL(10,2) NOT NULL DEFAULT 0,
  `valor_piscina` DECIMAL(10,2) NOT NULL DEFAULT 0,
  `valor_jardim` DECIMAL(10,2) NOT NULL DEFAULT 0,
  `valor_hidraulico` DECIMAL(10,2) NOT NULL DEFAULT 0,
  `cidade` VARCHAR(100) NULL,
  `bairro` VARCHAR(100) NULL,
  `mp_email` VARCHAR(180) NULL,
  `avaliacao_media` DECIMAL(3,2) NOT NULL DEFAULT 0,
  `total_avaliacoes` INT UNSIGNED NOT NULL DEFAULT 0,
  `disponivel` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NULL,
  `updated_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `fk_perfis_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `enderecos` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `usuario_id` INT UNSIGNED NOT NULL,
  `titulo` VARCHAR(60) NOT NULL DEFAULT 'Casa',
  `cep` VARCHAR(10) NULL,
  `logradouro` VARCHAR(180) NOT NULL,
  `numero` VARCHAR(20) NULL,
  `complemento` VARCHAR(80) NULL,
  `bairro` VARCHAR(100) NOT NULL,
  `cidade` VARCHAR(100) NOT NULL,
  `uf` CHAR(2) NOT NULL,
  `principal` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NULL,
  `updated_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `fk_enderecos_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `agendamentos` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `cliente_id` INT UNSIGNED NOT NULL,
  `prestador_id` INT UNSIGNED NOT NULL,
  `tipo_servico` ENUM('diarista','passeador','telhado','piscinas','jardins','hidraulico') NOT NULL,
  `endereco_id` INT UNSIGNED NULL,
  `data_servico` DATE NOT NULL,
  `hora_inicio` TIME NOT NULL,
  `duracao_horas` DECIMAL(4,1) NOT NULL DEFAULT 4.0,
  `valor_total` DECIMAL(10,2) NOT NULL,
  `comissao_percentual` DECIMAL(5,2) NOT NULL DEFAULT 15.00,
  `comissao_valor` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `valor_prestador` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `status` ENUM('pendente','aceito','rejeitado','cancelado','em_andamento','aguardando_confirmacao','confirmado','pago') NOT NULL DEFAULT 'pendente',
  `observacoes_cliente` TEXT NULL,
  `motivo_rejeicao` TEXT NULL,
  `aceito_em` DATETIME NULL,
  `iniciado_em` DATETIME NULL,
  `concluido_em` DATETIME NULL,
  `confirmado_em` DATETIME NULL,
  `pago_em` DATETIME NULL,
  `created_at` DATETIME NULL,
  `updated_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  KEY `cliente_id` (`cliente_id`),
  KEY `prestador_id` (`prestador_id`),
  KEY `status` (`status`),
  KEY `data_servico` (`data_servico`),
  CONSTRAINT `fk_ag_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_ag_prestador` FOREIGN KEY (`prestador_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_ag_endereco` FOREIGN KEY (`endereco_id`) REFERENCES `enderecos` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `pagamentos` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `agendamento_id` INT UNSIGNED NOT NULL,
  `gateway` VARCHAR(40) NOT NULL DEFAULT 'mock',
  `gateway_ref` VARCHAR(120) NULL,
  `mp_preference_id` VARCHAR(80) NULL,
  `mp_payment_id` VARCHAR(80) NULL,
  `mp_status` VARCHAR(40) NULL,
  `checkout_url` VARCHAR(500) NULL,
  `status` ENUM('pendente','autorizado','capturado','estornado','falhou') NOT NULL DEFAULT 'pendente',
  `valor_bruto` DECIMAL(10,2) NOT NULL,
  `valor_comissao` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `valor_liquido_prestador` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `autorizado_em` DATETIME NULL,
  `capturado_em` DATETIME NULL,
  `payout_status` ENUM('nao_aplicavel','pendente','processando','pago','falhou') NOT NULL DEFAULT 'nao_aplicavel',
  `payout_ref` VARCHAR(120) NULL,
  `payout_em` DATETIME NULL,
  `payout_nota` TEXT NULL,
  `meta_json` TEXT NULL,
  `created_at` DATETIME NULL,
  `updated_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `agendamento_id` (`agendamento_id`),
  KEY `status` (`status`),
  CONSTRAINT `fk_pag_ag` FOREIGN KEY (`agendamento_id`) REFERENCES `agendamentos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `configuracoes` (
  `chave` VARCHAR(80) NOT NULL,
  `valor` TEXT NULL,
  `updated_at` DATETIME NULL,
  PRIMARY KEY (`chave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `configuracoes` (`chave`, `valor`, `updated_at`) VALUES
('comissao_percentual', '15', NOW()),
('mp_auto_payout', '0', NOW());

CREATE TABLE `avaliacoes` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `agendamento_id` INT UNSIGNED NOT NULL,
  `cliente_id` INT UNSIGNED NOT NULL,
  `prestador_id` INT UNSIGNED NOT NULL,
  `nota` TINYINT UNSIGNED NOT NULL,
  `comentario` TEXT NULL,
  `created_at` DATETIME NULL,
  `updated_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `agendamento_id` (`agendamento_id`),
  KEY `prestador_id` (`prestador_id`),
  CONSTRAINT `fk_av_ag` FOREIGN KEY (`agendamento_id`) REFERENCES `agendamentos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_av_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_av_prestador` FOREIGN KEY (`prestador_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Senha de todos: demo123
INSERT INTO `usuarios` (`nome`, `email`, `senha`, `telefone`, `role`, `ativo`, `created_at`, `updated_at`) VALUES
('Admin Casa em Dia', 'admin@demo.com', '$2y$10$vTS8mO8S0DvFUeGvCNww8.DH3KqUImLtiEpz7dMBQzRBSDY8AauNK', '(11) 90000-0001', 'admin', 1, NOW(), NOW()),
('Ana Cliente', 'cliente@demo.com', '$2y$10$vTS8mO8S0DvFUeGvCNww8.DH3KqUImLtiEpz7dMBQzRBSDY8AauNK', '(11) 91111-1111', 'cliente', 1, NOW(), NOW()),
('Bruno Diarista', 'prestador@demo.com', '$2y$10$vTS8mO8S0DvFUeGvCNww8.DH3KqUImLtiEpz7dMBQzRBSDY8AauNK', '(11) 92222-2222', 'prestador', 1, NOW(), NOW()),
('Carla Passeadora', 'passeadora@demo.com', '$2y$10$vTS8mO8S0DvFUeGvCNww8.DH3KqUImLtiEpz7dMBQzRBSDY8AauNK', '(11) 93333-3333', 'prestador', 1, NOW(), NOW());

INSERT INTO `perfis_prestador` (`usuario_id`, `tipos_servico`, `bio`, `valor_diaria`, `valor_passeio`, `valor_telhado`, `valor_piscina`, `valor_jardim`, `valor_hidraulico`, `cidade`, `bairro`, `avaliacao_media`, `total_avaliacoes`, `disponivel`, `created_at`, `updated_at`)
SELECT u.id, 'diarista,jardins', 'Diarista e jardinagem leve. 8 anos em apartamentos e casas.', 180.00, 0, 0, 0, 120.00, 0, 'São Paulo', 'Vila Mariana', 4.80, 12, 1, NOW(), NOW()
FROM `usuarios` u WHERE u.email = 'prestador@demo.com';

INSERT INTO `perfis_prestador` (`usuario_id`, `tipos_servico`, `bio`, `valor_diaria`, `valor_passeio`, `valor_telhado`, `valor_piscina`, `valor_jardim`, `valor_hidraulico`, `cidade`, `bairro`, `avaliacao_media`, `total_avaliacoes`, `disponivel`, `created_at`, `updated_at`)
SELECT u.id, 'passeador,diarista,piscinas', 'Pets, diárias leves e manutenção básica de piscina.', 160.00, 45.00, 0, 150.00, 0, 0, 'São Paulo', 'Pinheiros', 4.90, 20, 1, NOW(), NOW()
FROM `usuarios` u WHERE u.email = 'passeadora@demo.com';

INSERT INTO `enderecos` (`usuario_id`, `titulo`, `cep`, `logradouro`, `numero`, `complemento`, `bairro`, `cidade`, `uf`, `principal`, `created_at`, `updated_at`)
SELECT u.id, 'Casa', '04101-000', 'Rua Domingos de Morais', '1000', 'Apto 42', 'Vila Mariana', 'São Paulo', 'SP', 1, NOW(), NOW()
FROM `usuarios` u WHERE u.email = 'cliente@demo.com';
