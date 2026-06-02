export function getPrizeHits(modality) {
    switch (modality?.code) {
        case 'quina':
            return [2, 3, 4, 5];
        case 'mega_sena':
            return [4, 5, 6];
        case 'lotofacil':
            return [11, 12, 13, 14, 15];
        case 'lotomania':
            return [16, 17, 18, 19, 20];
        default:
            return Array.from({ length: modality?.draw_count || 0 }, (_, index) => index + 1).filter((hit) => hit >= 2);
    }
}

export function getPlayInstruction(modality) {
    switch (modality?.code) {
        case 'lotofacil':
            return 'Você marca entre 15 e 20 números, dentre os 25 disponíveis, e fatura o prêmio se acertar 11, 12, 13, 14 ou 15 números.';
        case 'quina':
            return 'Você marca entre 5 e 15 números, dentre os 80 disponíveis, e acompanha a leitura histórica dos acertos de 2 a 5 números.';
        case 'mega_sena':
            return 'Escolha entre 6 e 20 números, dentre os 60 disponíveis, e compare seu jogo com o histórico da modalidade.';
        default:
            return `Escolha entre ${modality?.bet_min_count ?? '?'} e ${modality?.bet_max_count ?? '?'} números.`;
    }
}

export function supportsCaixaOperations(modality) {
    return ['quina', 'lotofacil', 'mega_sena'].includes(modality?.code);
}

export function supportsSmartGeneration(modality) {
    return ['quina', 'lotofacil', 'mega_sena'].includes(modality?.code);
}

export function getImportHelp(modality) {
    if (modality?.code === 'lotofacil') {
        return 'Aceita arquivos XLSX e XLS para importar concursos da Lotofácil.';
    }

    if (modality?.code === 'quina') {
        return 'Aceita arquivos XLSX e XLS para importar concursos da Quina.';
    }

    if (modality?.code === 'mega_sena') {
        return 'Aceita arquivos XLSX e XLS para importar concursos da Mega-Sena.';
    }

    return `Aceita arquivos XLSX e XLS para importar concursos de ${modality?.name ?? 'loteria'}.`;
}

export function getHistoryPrizeEmptyText(modality) {
    const hits = getPrizeHits(modality);
    if (hits.length === 0) {
        return 'Essa combinação ainda não apareceu com faixa premiável no histórico disponível.';
    }

    return `Essa combinação ainda não apareceu com ${hits[0]} ou mais acertos premiáveis no histórico disponível.`;
}
