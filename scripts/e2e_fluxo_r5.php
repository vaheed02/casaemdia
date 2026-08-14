<?php

/**
 * Atalho: php scripts/e2e_fluxo_r5.php
 * Prefira: php spark e2e:pagamento
 */
passthru('php "' . dirname(__DIR__) . '/spark" e2e:pagamento ' . implode(' ', array_slice($argv, 1)));
