# JetEngine International Phone Field

Plugin WordPress que adiciona um campo de telefone internacional ao módulo de Forms (legacy) do JetEngine, utilizando a biblioteca [intl-tel-input](https://intl-tel-input.com/).

## Características

- 🌍 Suporte a mais de 200 países
- ✅ Validação em tempo real
- 📱 Dropdown pesquisável com bandeiras
- 💾 Salva no formato E.164 (ex: `+5511999999999`)
- 🎨 Totalmente customizável
- 📦 Carrega biblioteca via CDN (sem peso adicional)
- 🔄 Suporte a formulários AJAX
- 🎯 Compatível com Elementor popups

## Requisitos

- WordPress 5.6+
- PHP 7.4+
- JetEngine com módulo Forms ativo

## Instalação

1. Faça download do plugin (arquivo ZIP)
2. No WordPress, vá em **Plugins > Adicionar Novo > Enviar Plugin**
3. Selecione o arquivo ZIP e clique em **Instalar Agora**
4. Ative o plugin

## Uso

### Adicionando o Campo ao Formulário

1. Vá em **JetEngine > Forms**
2. Crie ou edite um formulário
3. Adicione um novo campo
4. Selecione o tipo **"International Phone"**
5. Configure as opções desejadas

### Opções de Configuração

| Opção | Descrição | Padrão |
|-------|-----------|--------|
| **País Inicial** | Código ISO do país selecionado inicialmente | `br` |
| **Países Preferidos** | Lista de países que aparecem no topo do dropdown | `br,us,pt` |
| **Apenas Países** | Limita a lista apenas aos países especificados | vazio (todos) |
| **Permitir Dropdown** | Permite trocar de país via dropdown | Sim |
| **Separar Código DDI** | Exibe o código DDI separado do input | Não |
| **Formatar ao Digitar** | Formata o número enquanto o usuário digita | Sim |
| **Validar Telefone** | Valida se o número é válido para o país | Sim |

### Códigos de País

Use códigos ISO 3166-1 alpha-2 (duas letras, minúsculas):

- Brasil: `br`
- Estados Unidos: `us`
- Portugal: `pt`
- Argentina: `ar`
- México: `mx`

Lista completa: [Wikipedia - ISO 3166-1 alpha-2](https://en.wikipedia.org/wiki/ISO_3166-1_alpha-2)

## Formato de Salvamento

O número é sempre salvo no formato **E.164**, que é o padrão internacional:

```
+[código do país][número]
```

Exemplos:
- Brasil: `+5511999999999`
- EUA: `+14155552671`
- Portugal: `+351912345678`

## Validação

O plugin realiza validação em dois níveis:

1. **Frontend (JavaScript)**: Validação em tempo real usando a biblioteca libphonenumber
2. **Backend (PHP)**: Validação básica do formato E.164 antes de salvar

### Mensagens de Erro

- Número inválido
- Código de país inválido
- Número muito curto
- Número muito longo

## Integração com Notificações

Ao usar notificações do JetEngine (email, webhook, etc.), o valor do campo virá no formato E.164:

```
Campo: phone_field
Valor: +5511999999999
```

## Customização CSS

O plugin adiciona classes CSS que você pode usar para estilização:

```css
/* Wrapper do campo */
.jeipf-field-wrapper { }

/* Campo com erro */
.jeipf-field-wrapper.jeipf-has-error { }

/* Campo válido */
.jeipf-field-wrapper.jeipf-is-valid { }

/* Mensagem de validação */
.jeipf-validation-message { }
.jeipf-validation-message.jeipf-valid { }
.jeipf-validation-message.jeipf-invalid { }
```

## JavaScript API

O plugin expõe uma API JavaScript para uso avançado:

```javascript
// Reinicializar um campo específico
JEIPF.reinit(document.querySelector('.jeipf-field-wrapper'));

// Acessar todas as instâncias
JEIPF.instances.forEach(instance => {
    console.log(instance.iti.getNumber());
});
```

## Hooks Disponíveis

### PHP Filters

```php
// Modificar tipos de campo
add_filter('jet-engine/forms/booking/field-types', function($types) {
    // Seu código
    return $types;
});

// Processar valor antes de salvar
add_filter('jet-engine/forms/handler/form-data', function($data, $handler) {
    // Seu código
    return $data;
}, 10, 2);
```

## Troubleshooting

### Dropdown não aparece em popups

Se o dropdown não aparecer corretamente em popups do Elementor, adicione este CSS:

```css
.elementor-popup-modal .iti__dropdown {
    z-index: 100001 !important;
}
```

### Conflito com outros plugins de máscara

Se houver conflito com outros plugins de máscara de telefone, desative-os para campos que usam o International Phone.

### Validação não funciona

Verifique se o script de utilitários está carregando corretamente. Abra o console do navegador e procure por erros relacionados a "utils.js".

## Changelog

### 1.0.3
- Corrigido hook de renderização para `jet-engine/forms/booking/field-template/{tipo}`
- Campo agora recebe corretamente os dados ($args) do JetEngine
- Formulário envia corretamente com o campo de telefone internacional

### 1.0.2
- Corrigido hook de renderização do campo (`pre-render-field`)
- Removida verificação desnecessária do módulo Forms Legacy
- Simplificado código principal

### 1.0.1
- Melhorada detecção do módulo Forms Legacy

### 1.0.0
- Versão inicial
- Suporte a JetEngine Forms (legacy)
- Integração com intl-tel-input v25.3.1
- Validação em tempo real
- Múltiplas opções de configuração

## Licença

GPL-2.0+

## Autor

Desenvolvido por Vitoor
