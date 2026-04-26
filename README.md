# 🎰 ABS Loterias

> Plataforma inteligente para análise estatística e geração de jogos de loteria, integrada ao ecossistema **ABS Gestão Financeira**.

---

## 🧭 Visão Geral

O **ABS Loterias** foi projetado com foco em:

* 🧠 Inteligência estatística aplicada a jogos
* ⚡ Alta performance com engine dedicada em Go
* 🧩 Arquitetura modular e escalável
* 🔗 Integração futura com dados financeiros (ABS)

---

## 🏗️ Arquitetura de Alto Nível

```text
┌──────────────────────────────┐
│        Frontend (React)      │
│  Inertia.js + TailwindCSS    │
└──────────────┬───────────────┘
               │ HTTP (REST)
┌──────────────▼───────────────┐
│      Backend (Laravel)       │
│  Controllers / Services      │
│  Regras de negócio           │
└──────────────┬───────────────┘
               │ HTTP (Internal API)
┌──────────────▼───────────────┐
│   Engine Inteligente (Go)    │
│  Algoritmos / Score / IA     │
└──────────────┬───────────────┘
               │
        ┌──────▼──────┐
        │   MySQL     │
        │  Persistência│
        └─────────────┘
```

---

## 🧠 Decisão Arquitetural (Por quê Go + PHP?)

| Camada   | Tecnologia | Motivo                      |
| -------- | ---------- | --------------------------- |
| Frontend | React      | UI moderna e reativa        |
| Backend  | Laravel    | Produtividade + ecossistema |
| Engine   | Go         | Performance e concorrência  |
| Banco    | MySQL      | Consistência e simplicidade |

👉 **Separação crítica:**

* PHP → regras de negócio e orquestração
* Go → processamento pesado (estatística e geração)

---

## 🔄 Fluxo de Geração de Jogos

```text
[Usuário]
   ↓
Seleciona modalidade + score
   ↓
[Frontend React]
   ↓
POST /generate-smart
   ↓
[Laravel Controller]
   ↓
[Service Layer]
   ↓
Chamada HTTP → Engine Go
   ↓
[Algoritmo Inteligente]
   ↓
Cálculo de Score
   ↓
Retorno dos jogos
   ↓
[Laravel Response]
   ↓
[Frontend renderiza]
```

---

## 🧩 Estrutura de Código

### Backend (Laravel)

```text
app/
├── Http/
│   ├── Controllers/
│   └── Requests/
├── Services/
├── Models/
└── Actions/
```

### Frontend (React)

```text
resources/js/
├── Pages/
├── Components/
└── Layouts/
```

### Engine (Go)

```text
lottery-engine/
├── internal/
│   ├── engine/
│   ├── models/
│   └── analysis/
├── http/
└── main.go
```

---

## 🧠 Engine Inteligente (Core do Sistema)

### Endpoint Principal

```http
POST /generate-smart
```

### Responsabilidades

* Geração de combinações válidas
* Cálculo de score baseado em:

  * Frequência histórica
  * Distribuição de números
  * Evitar padrões ruins
* Otimização de performance

---

## 📊 Modelo de Score

O score é baseado em múltiplos fatores:

* 📈 Frequência histórica
* ⚖️ Balanceamento (pares/ímpares)
* 🔢 Distribuição por faixa
* 🚫 Penalização de padrões comuns

> ⚠️ Scores acima de 90 são estatisticamente raros

---

## 🔁 Análise de Combinações Repetidas

```http
GET /modalities/{modality}/repeated-combinations
```

### Objetivo:

* Identificar repetições históricas
* Validar padrões raros
* Suporte a análises avançadas

---

## 📥 Pipeline de Importação

```text
Upload CSV/Excel
      ↓
Validação de estrutura
      ↓
Normalização dos dados
      ↓
Verificação de duplicidade
      ↓
Persistência no banco
```

---

## ⚙️ Execução Local

### 🐳 Docker

```bash
docker-compose up -d
```

---

### Backend

```bash
php artisan migrate
php artisan serve
```

---

### Frontend

```bash
npm install
npm run dev
```

---

### Engine GO

```bash
cd lottery-engine
go run main.go
```

---

## 🧪 Estratégia de Testes

### Backend

* Feature Tests
* Testes de importação
* Validação de regras

### Engine

* Testes unitários
* Testes de performance

---

## 📊 Escalabilidade

### Horizontal

* Engine Go pode escalar separadamente
* Possibilidade de múltiplas instâncias

### Vertical

* Backend Laravel pode ser otimizado com cache
* Uso futuro de Redis

---

## 🔐 Considerações Técnicas

* API interna desacoplada
* Engine isolada (facilita evolução para IA)
* Sistema preparado para:

  * Machine Learning
  * Processamento paralelo
  * Alto volume de dados

---

## 🛣️ Roadmap Estratégico

### Curto Prazo

* [ ] UX refinada
* [ ] Exibição visual de score
* [ ] Botão de combinações repetidas

---

### Médio Prazo

* [ ] Cache inteligente
* [ ] Otimização do algoritmo
* [ ] Dashboard estatístico

---

### Longo Prazo

* [ ] Machine Learning
* [ ] Previsão probabilística
* [ ] Integração com comportamento do usuário

---

## 🔗 Integração com ABS

Futuras integrações:

* Controle financeiro de apostas
* ROI por modalidade
* Análise de performance do usuário

---

## 🧱 Princípios de Engenharia Aplicados

* Separation of Concerns
* Single Responsibility
* API First
* Escalabilidade horizontal
* Fail-safe (fallback de score)

---

## 👨‍💻 Autor

**Adriano Felix de Freitas**

* Desenvolvedor Web
* Especialista em sistemas de alta complexidade
* Criador do ecossistema ABS

---

## 📄 Licença

Projeto privado — uso interno ABS

---

## ⭐ Considerações Finais

O **ABS Loterias** já se encontra em um estágio avançado:

* Arquitetura sólida
* Engine desacoplada
* Base pronta para IA

👉 Projeto preparado para evoluir de **estatística → inteligência preditiva**
