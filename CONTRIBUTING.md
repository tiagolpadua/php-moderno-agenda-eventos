# Como contribuir

## Fluxo de trabalho

Usamos **GitHub flow** com branches curtas, próximo do *trunk-based development*:

1. Toda mudança começa com uma **issue**.
2. Crie uma branch a partir da `main` atualizada, com o nome `tipo/descricao-curta` (ex.: `feat/busca-eventos`, `fix/data-invalida`).
3. Faça commits pequenos, seguindo [Conventional Commits](https://www.conventionalcommits.org/pt-br/v1.0.0/) (`feat:`, `fix:`, `docs:`, `test:`, `ci:`, `chore:`).
4. Abra o **pull request** cedo (pode ser *draft*) com `Closes #N` na descrição.
5. O merge na `main` só acontece com **CI verde** e **1 revisão aprovada**.
6. A branch vive **no máximo 2 dias**. Se a funcionalidade for maior, integre aos poucos usando um **feature toggle** desligado.

## Regras da `main`

- A `main` **sempre** está pronta para ir para produção.
- **Ninguém faz push direto na `main`.** Tudo entra por PR.
- Quebrou a `main`? Conserte em até 15 minutos **ou** reverta o commit (`git revert`) e conserte com calma numa branch.

## Definição de pronto

- [ ] Critérios de aceite da issue atendidos
- [ ] Testes automatizados cobrindo a mudança, e `composer test` passando
- [ ] CI verde no PR
- [ ] Revisão aprovada
- [ ] Documentação atualizada (README, `.env.example`), se necessário
- [ ] Funcionalidade incompleta protegida por feature toggle **desligado**

## Feature toggles

| Variável | Recurso | Situação |
|---|---|---|
| `FEATURE_BUSCA` | Busca de eventos por título ou local (`/?q=` e `/api/eventos?q=`) | Em liberação |

Para ligar localmente: `FEATURE_BUSCA=true composer serve`.
Remova o toggle (e o código antigo) assim que o recurso estiver liberado para todos: toggle velho é dívida técnica.

## Responsabilidades

| Papel | Responsabilidade |
|---|---|
| Autor do PR | Manter o PR pequeno, explicar o "porquê", responder à revisão, garantir o CI verde |
| Revisor | Revisar em até 1 dia útil, focando em correção, legibilidade e testes |
| Time | Tratar a `main` vermelha como prioridade máxima |
