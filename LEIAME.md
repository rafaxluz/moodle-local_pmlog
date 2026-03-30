# Process Mining Log Builder (`local_pmlog`)

## Visão Geral

Process Mining Log Builder é um plugin local do Moodle para extração, normalização, armazenamento e exportação de eventos de curso voltados à mineração de processos.

O plugin transforma eventos brutos do log padrão do Moodle em um log de atividades estruturado, que pode ser consultado dentro do Moodle e exportado para ferramentas externas.

## Funcionalidades Principais

- Geração manual de logs no nível do curso
- Processamento em segundo plano com tarefas ad-hoc do Moodle
- Execução agendada com tarefas agendadas do Moodle
- Deduplicação sequencial com janelas de tempo configuráveis
- Visualização de timeline por aluno dentro do Moodle
- Exportação nos formatos CSV e XES
- Modos de exportação genérico, anonimizado e nomeado

## Estado Atual

- Versão: `1.0.1`
- Maturidade: `STABLE`
- Componente: `local_pmlog`

## Requisitos

- Moodle `4.1` ou superior
- Uma versão de PHP compatível com a instalação alvo do Moodle
- Cron do Moodle configurado e em execução

## Instalação

1. Copie o plugin para `local/pmlog`.
2. Acesse **Administração do site > Notificações**.
3. Conclua o processo de instalação.

## Acesso

### Acesso no Curso

Usuários com a capability `local/pmlog:manage` podem acessar o plugin pela navegação do curso.

Arquétipos permitidos por padrão:

- `manager`
- `editingteacher`
- `teacher`

### Administração do Site

O plugin adiciona uma única entrada administrativa em:

- **Administração do site > Plugins > Plugins locais > Process Mining Log Builder**

Essa página é utilizada para configurar execuções agendadas.

## Processamento Manual

No nível do curso, o plugin permite:

- definir datas opcionais de início e fim;
- limitar o processamento apenas às ações de alunos;
- habilitar deduplicação sequencial;
- habilitar remoção mais rígida de duplicatas da mesma atividade;
- configurar janelas de deduplicação e colapso de visualizações;
- gerar arquivos CSV e XES nos modos genérico, anonimizado e nomeado.

Após o envio do formulário, o processamento é enfileirado como tarefa ad-hoc e executado pelo cron do Moodle.

A página do curso também apresenta:

- status de execução;
- links para os últimos artefatos exportados;
- resumo das configurações da última execução;
- contagem de eventos por aluno;
- links para timelines individuais.

## Processamento Agendado

A execução agendada é configurada pela página administrativa indicada acima.

As opções disponíveis incluem:

- habilitar ou desabilitar execuções agendadas;
- selecionar os cursos que serão processados;
- habilitar filtro apenas para alunos;
- configurar o comportamento de deduplicação;
- definir janelas de deduplicação e colapso de visualizações;
- habilitar geração de CSV e XES nos modos genérico, anonimizado e nomeado.

A tarefa agendada está definida em `db/tasks.php` e, por padrão, é executada diariamente às `02:00`. O agendamento pode ser ajustado na administração de tarefas agendadas do Moodle.

## Dados Armazenados

Os eventos normalizados são armazenados na tabela `{local_pmlog_events}`.

Campos relevantes:

- `courseid`
- `userid`
- `caseid`
- `activity`
- `activitygroup`
- `timecreated`
- `cmid`
- `component`
- `eventname`
- `action`
- `target`
- `metajson`

## Definição de Caso

O plugin agrupa os eventos por aluno dentro do curso. Na prática, cada trace exportado representa um usuário em um curso.

## Formatos de Exportação

### CSV

O exportador CSV inclui:

- `caseid`
- `activity`
- `timestamp`
- `userid`
- `courseid`
- `cmid`
- `component`
- `eventname`
- `action`
- `target`

Modos disponíveis no CSV:

- `Generic`: exporta o rótulo normalizado da atividade.
- `Anonymized`: exporta o rótulo normalizado enriquecido com um marcador anonimizado do módulo, como `Quiz open [cmid:42]`.
- `Nomeado`: exporta o rótulo normalizado enriquecido com o nome real da atividade, como `Quiz open: Week 1 Quiz`.

### XES

O exportador XES usa um trace por `caseid` e um evento por linha do log normalizado.

O XES genérico inclui:

- `concept:name` no nível do log usando um identificador neutro do curso, como `course:123`
- `concept:name` no nível do trace com base no `caseid`
- `concept:name` no nível do evento com base na atividade normalizada
- `time:timestamp`
- `org:resource` derivado de `userid`
- `userid`
- `courseid`
- `cmid`, `activitygroup`, `component`, `eventname`, `action` e `target` quando disponíveis

Modos adicionais no XES:

- `Anonymized`: enriquece o `concept:name` do evento com um marcador anonimizado do módulo.
- `Nomeado`: usa nomes reais do curso no nível de log e trace e enriquece o `concept:name` do evento com o nome real da atividade quando disponível.

## Privacidade

Este plugin processa dados pessoais derivados dos logs de atividade do Moodle.

- Lê dados de eventos dos logs padrão do Moodle.
- Armazena uma cópia normalizada em `{local_pmlog_events}`.
- Disponibiliza visualizações internas de timeline para usuários autorizados.
- Arquivos CSV e XES incluem identificadores numéricos de usuário e curso.
- Exportações nomeadas podem incluir nomes reais de cursos e atividades.

O plugin inclui implementação da Privacy API do Moodle.

## Licença

Copyright 2026 rafaxluz

Licenciado sob GNU GPL v3 ou posterior.
