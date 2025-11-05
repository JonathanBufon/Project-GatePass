/*
 * Lógica para gerenciar coleções de formulário dinâmicas (Padrão Symfony)
 * Usado em 'novo.html.twig' para adicionar/remover Lotes.
 */
document.addEventListener('DOMContentLoaded', () => {

    const addFormToCollection = (e) => {
        const collectionHolder = document.querySelector('#' + e.currentTarget.dataset.collectionHolderId);
        const prototype = collectionHolder.dataset.prototype;
        const index = collectionHolder.dataset.index;

        let newForm = prototype;

        // Substitui o placeholder (ex: __name__) pelo índice atual
        newForm = newForm.replace(/__name__/g, index);

        collectionHolder.dataset.index = parseInt(index) + 1;

        // Cria um 'div' temporário
        const tempDiv = document.createElement('div');

        // Insere o HTML do protótipo (que já vem pronto com o grid)
        tempDiv.innerHTML = newForm;

        // Adiciona o item
        collectionHolder.appendChild(tempDiv.firstElementChild);
    };

    const removeFormFromCollection = (e) => {
        // Busca pelo 'lote-item' (definido no bloco Twig)
        if (e.target.classList.contains('remove-lote-button') || e.target.closest('.remove-lote-button')) {
            e.target.closest('.lote-item').remove();
        }
    };

    // Event listener para Adicionar
    document.querySelectorAll('.add-lote-button').forEach(btn => {
        btn.addEventListener("click", addFormToCollection);
    });

    // Event listener para Remover (usando delegação de evento)
    // Usamos um seletor mais genérico 'body' para o caso do wrapper não existir no load
    document.querySelector('body').addEventListener('click', removeFormFromCollection);
});