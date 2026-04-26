# 🎰 ABS Loterias

Sistema inteligente para análise e geração de jogos de loteria, integrado ao ecossistema **ABS Gestão Financeira**.

---

## 📌 Sobre o Projeto

O **ABS Loterias** é uma plataforma desenvolvida para:

* 🎯 Gerar jogos com base em análise estatística
* 📊 Analisar resultados históricos de loterias
* 🔁 Identificar padrões e repetições
* 🧠 Aplicar algoritmos inteligentes para sugestão de jogos

Arquitetura híbrida focada em performance e escalabilidade.

---

## 🧠 Arquitetura

```text
Frontend (React + Inertia)
        ↓
Backend (Laravel API)
        ↓
Engine Inteligente (Go)
        ↓
Banco de Dados (MySQL)
```

---

## ⚙️ Tecnologias Utilizadas

### Backend

* PHP 8.2
* Laravel 12
* MySQL

### Engine de Cálculo

* Golang

### Frontend

* React
* Inertia.js
* TailwindCSS

### Infraestrutura

* Docker
* Nginx
* Vite

---

## 🚀 Funcionalidades

### 🎰 Modalidades

* Mega-Sena
* Quina
* Lotofácil
  *(Estrutura preparada para novas modalidades)*

---

### 📥 Importação de Resultados

* Upload manual via planilha
* Validação de dados
* Prevenção de duplicidade

---

### 🧠 Geração Inteligente de Jogos

Endpoint:

```http
POST /generate-smart
```

Parâmetros:

```json
{
  "modality": "quina",
  "games": 5,
  "min_score": 85
}
```

#### Regras:

* Gera jogos com base em análise histórica
* Aplica score de qualidade
* Caso não encontre jogos com score mínimo:

  * Retorna os melhores disponíveis

---

### 📊 Sistema de Score

Os jogos recebem pontuação baseada em:

* Frequência dos números
* Distribuição equilibrada
* Padrões estatísticos

> ⚠️ Scores acima de 90 são extremamente raros

---

### 🔁 Combinações Repetidas

Endpoint:

```http
GET /modalities/{modality}/repeated-combinations
```

Permite identificar:

* Jogos que já se repetiram na história
* Padrões raros

---

### 🎲 Minhas Apostas

* Visualização de jogos gerados
* Filtros por período
* Histórico do usuário

---

## 🐳 Ambiente com Docker

### Subir ambiente

```bash
docker-compose up -d
```

---

### Backend (Laravel)

```bash
docker exec -it app php artisan migrate
docker exec -it app php artisan serve --host=0.0.0.0 --port=8000
```

---

### Frontend

```bash
npm install
npm run dev
```

---

### Engine Go

```bash
cd lottery-engine
go run main.go
```

---

## 🧪 Testes

### Laravel

```bash
php artisan test
```

---

### Go

```bash
go test ./...
```

---

## 📂 Estrutura do Projeto

```text
app/
 ├── Http/
 ├── Models/
 ├── Services/

resources/js/
 ├── Pages/
 ├── Components/

lottery-engine/
 ├── internal/
 ├── engine/
 ├── models/
```

---

## ⚠️ Problemas Conhecidos

* Scores muito altos (90+) são difíceis de alcançar
* Dependência de histórico para melhor performance
* Possíveis ajustes visuais no frontend

---

## 🛣️ Roadmap

### 🔥 Prioridade

* [ ] Botão de combinações repetidas no frontend
* [ ] Melhor UX na geração de jogos
* [ ] Exibição visual do score

---

### 🧠 Inteligência

* [ ] Machine Learning para previsão
* [ ] Score baseado no usuário
* [ ] Sugestão automática de jogos

---

### 📊 Estatísticas

* [ ] Números quentes e frios
* [ ] Frequência por período
* [ ] Gráficos interativos

---

## 🔗 Integração com ABS Financeiro

Possibilidades futuras:

* Controle de gastos com apostas
* ROI por modalidade
* Histórico financeiro de jogos

---

## 🤝 Contribuição

1. Fork o projeto
2. Crie uma branch:

```bash
git checkout -b feature/minha-feature
```

3. Commit:

```bash
git commit -m "feat: minha nova feature"
```

4. Push:

```bash
git push origin feature/minha-feature
```

5. Abra um Pull Request

---

## 📄 Licença

Este projeto é privado e pertence ao ecossistema **ABS**.

---

## 👨‍💻 Autor

**Adriano Felix de Freitas**

* Desenvolvedor Web
* Criador do ABS Gestão Financeira
