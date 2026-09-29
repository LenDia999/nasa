<?php
// index.php
require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;

$app = AppFactory::create();
$app->addBodyParsingMiddleware();


$missoes = [
    ['id' => 1, 'nome' => 'Apollo 11',  'ano' => 1969, 'agencia' => 'NASA', 'status' => 'Concluída'],
    ['id' => 2, 'nome' => 'Voyager 1',  'ano' => 1977, 'agencia' => 'NASA', 'status' => 'Em operação'],
    ['id' => 3, 'nome' => 'Artemis II', 'ano' => 2026, 'agencia' => 'NASA', 'status' => 'Planejada'],
    ['id' => 4, 'nome' => 'Sputnik 1',  'ano' => 1957, 'agencia' => 'URSS', 'status' => 'Concluída'],
    ['id' => 5, 'nome' => 'Curiosity',  'ano' => 2011, 'agencia' => 'NASA', 'status' => 'Em operação'],
];


$app->get('/status', function ($request, $response) {
    $response->getBody()->write(json_encode(['status' => 'ok']));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
});


$app->get('/missoes', function ($request, $response) use (&$missoes) {
    $response->getBody()->write(json_encode($missoes, JSON_UNESCAPED_UNICODE));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
});


$app->get('/missoes/{id}', function ($request, $response, $args) use (&$missoes) {
    $missao = current(array_filter($missoes, fn($m) => $m['id'] === (int) $args['id'])) ?: null;

    if (!$missao) {
        $response->getBody()->write(json_encode(['erro' => 'Missão não encontrada']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
    }

    $response->getBody()->write(json_encode($missao, JSON_UNESCAPED_UNICODE));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
});


$app->post('/missoes', function ($request, $response) use (&$missoes) {
    $dados = $request->getParsedBody();

  
    if (
        empty($dados['nome']) ||
        empty($dados['ano']) ||
        empty($dados['agencia']) ||
        empty($dados['status'])
    ) {
        $response->getBody()->write(json_encode([
            'erro' => 'Campos obrigatórios: nome, ano, agencia, status'
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

  
    $novoId = count($missoes) > 0
        ? max(array_column($missoes, 'id')) + 1
        : 1;

    $novaMissao = [
        'id' => $novoId,
        'nome' => $dados['nome'],
        'ano' => (int) $dados['ano'],
        'agencia' => $dados['agencia'],
        'status' => $dados['status'],
    ];

    $missoes[] = $novaMissao;

    $response->getBody()->write(json_encode($novaMissao, JSON_UNESCAPED_UNICODE));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
});



$app->put('/missoes/{id}', function ($request, $response, $args) use (&$missoes) {
    $dados = $request->getParsedBody();

    foreach ($missoes as &$missao) {
        if ($missao['id'] === (int) $args['id']) {

            // Atualiza apenas os campos enviados
            if (isset($dados['nome']))    $missao['nome']    = $dados['nome'];
            if (isset($dados['ano']))     $missao['ano']     = (int) $dados['ano'];
            if (isset($dados['agencia'])) $missao['agencia'] = $dados['agencia'];
            if (isset($dados['status']))  $missao['status']  = $dados['status'];

            $response->getBody()->write(json_encode($missao, JSON_UNESCAPED_UNICODE));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        }
    }
    unset($missao);


    $response->getBody()->write(json_encode(['erro' => 'Missão não encontrada']));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
});


$app->delete('/missoes/{id}', function ($request, $response, $args) use (&$missoes) {
    $id = (int) $args['id'];


    $existe = current(array_filter($missoes, fn($m) => $m['id'] === $id));

    if (!$existe) {
        $response->getBody()->write(json_encode(['erro' => 'Missão não encontrada']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
    }

    $missoes = array_values(array_filter($missoes, fn($m) => $m['id'] !== $id));
    return $response->withStatus(204);
});

$app->run();