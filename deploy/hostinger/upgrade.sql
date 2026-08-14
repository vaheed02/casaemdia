-- Casa em Dia — upgrade do banco (Hostinger)
-- Execute no phpMyAdmin no banco u240559973_diarista
-- Se alguma coluna já existir, ignore o erro "Duplicate column"

ALTER TABLE `perfis_prestador`
  ADD COLUMN `valor_telhado` DECIMAL(10,2) NOT NULL DEFAULT 0,
  ADD COLUMN `valor_piscina` DECIMAL(10,2) NOT NULL DEFAULT 0,
  ADD COLUMN `valor_jardim` DECIMAL(10,2) NOT NULL DEFAULT 0,
  ADD COLUMN `valor_hidraulico` DECIMAL(10,2) NOT NULL DEFAULT 0,
  ADD COLUMN `mp_email` VARCHAR(180) NULL;

ALTER TABLE `perfis_prestador`
  MODIFY `tipos_servico` VARCHAR(180) NOT NULL;

ALTER TABLE `agendamentos`
  MODIFY `tipo_servico` ENUM('diarista','passeador','telhado','piscinas','jardins','hidraulico') NOT NULL;

ALTER TABLE `pagamentos`
  ADD COLUMN `mp_preference_id` VARCHAR(80) NULL,
  ADD COLUMN `mp_payment_id` VARCHAR(80) NULL,
  ADD COLUMN `mp_status` VARCHAR(40) NULL,
  ADD COLUMN `checkout_url` VARCHAR(500) NULL,
  ADD COLUMN `payout_status` ENUM('nao_aplicavel','pendente','processando','pago','falhou') NOT NULL DEFAULT 'nao_aplicavel',
  ADD COLUMN `payout_ref` VARCHAR(120) NULL,
  ADD COLUMN `payout_em` DATETIME NULL,
  ADD COLUMN `payout_nota` TEXT NULL,
  ADD COLUMN `meta_json` TEXT NULL;

CREATE TABLE IF NOT EXISTS `configuracoes` (
  `chave` VARCHAR(80) NOT NULL,
  `valor` TEXT NULL,
  `updated_at` DATETIME NULL,
  PRIMARY KEY (`chave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `configuracoes` (`chave`, `valor`, `updated_at`) VALUES
('comissao_percentual', '15', NOW()),
('mp_auto_payout', '0', NOW());
