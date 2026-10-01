/*global gridjs, VuFind */
/**
 * BDTD: tabela das instituições participantes (datasources/home), montada com a faceta
 * instname_str da API de busca do próprio VuFind.
 *
 * Baseado no networks.js do legado; usa fetch em vez do axios e das funções do base.js.
 * A exportação gera o arquivo no navegador (o legado abria um endereço data:, que os
 * navegadores atuais bloqueiam).
 */
(function networksPage() {
  function setLoader(visible) {
    const loader = document.querySelector(".networks .loader");
    if (loader) {
      loader.style.display = visible ? "block" : "none";
    }
  }

  function showMessageError() {
    const wrapper = document.getElementById("networksWrapper");
    wrapper.innerHTML = '<div class="alert alert-danger" role="alert"></div>';
    wrapper.firstChild.textContent = "Não foi possível carregar as instituições. Tente novamente mais tarde.";
  }

  async function getAllInstitutions() {
    // VuFind.path só é definido pelo layout depois deste script
    const url = VuFind.path + "/api/v1/search?type=AllFields&facet[]=instname_str&sort=relevance&page=1&limit=0";
    const response = await fetch(url, { headers: { Accept: "application/json" } });
    if (!response.ok) {
      throw new Error("HTTP " + response.status);
    }
    const data = await response.json();
    return data.facets.instname_str;
  }

  function convertToCSV(allInstitutions) {
    let csv = "Instituição,Quantidade de itens\r\n";
    allInstitutions.forEach((item) => {
      csv += '"' + (item.value || "").replaceAll('"', '""') + '",' + item.count + "\r\n";
    });
    return csv;
  }

  function exportsCSV(allInstitutions) {
    const btnExport = document.querySelector(".btn-export-csv");
    btnExport.addEventListener("click", () => {
      // BOM para o Excel reconhecer o UTF-8
      const blob = new Blob(["\uFEFF" + convertToCSV(allInstitutions)], { type: "text/csv;charset=utf-8" });
      const link = document.createElement("a");
      link.href = URL.createObjectURL(blob);
      link.download = "instituicoes-participantes.csv";
      document.body.appendChild(link);
      link.click();
      link.remove();
      URL.revokeObjectURL(link.href);
    });
  }

  document.addEventListener("DOMContentLoaded", async () => {
    let allInstitutions;
    try {
      setLoader(true);
      allInstitutions = await getAllInstitutions();
    } catch (error) {
      console.error(error);
      showMessageError();
      return;
    } finally {
      setLoader(false);
    }
    new gridjs.Grid({
      columns: [
        {
          name: "Instituição",
          sort: true,
        },
        {
          name: "Documentos",
          sort: true,
        },
      ],
      search: true,
      pagination: {
        limit: 20,
        summary: false,
      },
      language: {
        search: {
          placeholder: "🔍 Buscar por...",
        },
        pagination: {
          previous: "Anterior",
          next: "Próximo",
          showing: "😃 Mostrando",
          results: () => "Resultado",
        },
      },
      data: allInstitutions.map((institution) => [
        institution.value,
        gridjs.html(
          "<a href='../Search/Results" + institution.href + "'>" + Number(institution.count) + "</a>"
        ),
      ]),
    }).render(document.getElementById("networksWrapper"));
    exportsCSV(allInstitutions);
  });
})();
