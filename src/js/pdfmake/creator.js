const writeRotatedText = (text, color) => {
    let canvas = document.createElement('canvas');
    let ctx = canvas.getContext('2d');

    canvas.width = 53;
    canvas.height = 300;

    ctx.font = 'bold 52pt Arial';
    ctx.rotate(-0.5 * Math.PI);
    ctx.fillStyle = color;
    ctx.fillText(text, -300, 52);

    return canvas.toDataURL();
};

function fillMargin(obj, margin) 
{
    if (margin)
    {
        obj.height += (margin[1] + margin[3]);
        obj.width += (margin[0] + margin[2]);
    }

    return obj;
}

function findInlineHeight(cell, maxWidth, usedWidth = 0) 
{
    if (cell._margin)
    {
        maxWidth = maxWidth - cell._margin[0] - cell._margin[2];
    }

    let calcLines = (inlines) => {
        if (!inlines) {
            return {
                height: 0,
                width: 0
            }
        }

        let currentMaxHeight = 0;
        let lastHadLineEnd = false;

        for (const currentNode of inlines) 
        {
            usedWidth += currentNode.width;

            if (usedWidth > maxWidth || lastHadLineEnd) 
            {
                currentMaxHeight += currentNode.height;
                usedWidth = currentNode.width;
            }
            
            else 
            {
                currentMaxHeight = Math.max(currentNode.height, currentMaxHeight);
            }

            lastHadLineEnd = !!currentNode.lineEnd;
        }

        return fillMargin({
            height: currentMaxHeight,
            width: usedWidth
        }, cell._margin)
    }
    
    if (cell._offsets)
    {
        usedWidth += cell._offsets.total;
    }

    if (cell._inlines && cell._inlines.length)
    {
        return calcLines(cell._inlines);
    } 
    
    else if (cell.stack && cell.stack[0])
    {
        return fillMargin(cell.stack.map(item => {
            return findInlineHeight(item, maxWidth);
        }).reduce((prev, next) => {
            return {
                height: prev.height + next.height,
                width: Math.max(prev.width + next.width)
            }
        }), cell._margin)
    } 
    
    else if (cell.ul)
    {
        return fillMargin(cell.ul.map(item => {
            return findInlineHeight(item, maxWidth);
        }).reduce((prev, next) => {
            return {
                height: prev.height + next.height,
                width: Math.max(prev.width + next.width)
            }
        }), cell._margin)
    } 
    
    else if (cell.table) 
    {
        let currentMaxHeight = 0;

        for (const currentTableBodies of cell.table.body) 
        {
            const innerTableHeights = currentTableBodies.map(mapTableBodies, maxWidth, usedWidth);
            currentMaxHeight = Math.max(...innerTableHeights, currentMaxHeight);
        }

        return fillMargin({
            height: currentMaxHeight,
            width: usedWidth
        }, cell._margin)
    } 
    
    else if (cell._height) 
    {
        usedWidth += cell._width;

        return fillMargin({
            height: cell._height,
            width: usedWidth
        }, cell._margin)
    }

    return fillMargin({
        height: null,
        width: usedWidth
    }, cell._margin)
}

function updateRowSpanCell(rowHeight, rowSpanCell) 
{
    for (let i = rowSpanCell.length - 1; i >= 0; i--) 
    {
        const rowCell = rowSpanCell[i];
        rowCell.maxHeight = rowCell.maxHeight + rowHeight;
        
        const {
            maxHeight,
            cellHeight,
            align,
            cell
        } = rowCell;

        rowCell.rowSpanCount = rowCell.rowSpanCount - 1;

        if (!rowCell.rowSpanCount) 
        {
            if (cellHeight) 
            {
                let topMargin;
                let cellAlign = align;
                
                if (cellAlign === 'bottom') 
                {
                    topMargin = maxHeight - cellHeight;
                } 
                
                else if (cellAlign === 'center') 
                {
                    topMargin = (maxHeight - cellHeight) / 2;
                }
                
                if (topMargin) 
                {
                    if (cell._margin) 
                    {
                        cell._margin[1] = cell._margin[1] + topMargin + 2;
                    } 
                    
                    else 
                    {
                        cell._margin = [0, topMargin + 2, 0, 0];
                    }
                }
            }

            rowSpanCell.splice(i, 1);
        }
    }
}

function applyVerticalAlignment(node, rowIndex, align, rowSpanCell, manualHeight = 0) 
{
    const allCellHeights = node.table.body[rowIndex].map(
        (innerNode, columnIndex) => {
            if (innerNode._span || innerNode.image) return null;

            const calcWidth = [...Array(innerNode.colSpan || 1).keys()].reduce((acc, i) => {
                return acc + node.table.widths[columnIndex + i]._calcWidth;
            }, 0)

            const mFindInlineHeight = findInlineHeight(innerNode, calcWidth, 0, rowIndex, columnIndex);
            
            return rowIndex == 1 ? 15.6 : mFindInlineHeight.height;
        }
    )
    
    let maxRowHeight = manualHeight ? manualHeight[rowIndex] : Math.max(...allCellHeights);

    node.table.body[rowIndex].forEach((cell, ci) => {
        if (cell.image)
        {
            cell._margin = [0, 2, 0, 0];
            return;
        }
        
        if (cell.rowSpan)
        {
            rowSpanCell.push({
                cell,
                rowSpanCount: cell.rowSpan,
                cellHeight: allCellHeights[ci],
                maxHeight: 0,
                align
            })

            return;
        }
        
        if (rowIndex == 0)
        {
            cell._margin = [0, 5, 0, 2];
            return;
        }

        if (rowIndex == 1)
        {
            cell._margin = [0, 5, 0, 0];
            return;
        }

        if (allCellHeights[ci])
        {
            let topMargin;
            let cellAlign = align;

            if (Array.isArray(align)) 
            {
                cellAlign = align[ci];
            }

            if (cellAlign === 'bottom') 
            {
                topMargin = maxRowHeight - allCellHeights[ci];
            } 
            
            else if (cellAlign === 'center') {
                topMargin = (maxRowHeight - allCellHeights[ci]) / 2;
            }

            topMargin = topMargin == 0 ? 2 : topMargin;

            if (topMargin) 
            {
                if (cell._margin) 
                {
                    cell._margin[1] += topMargin + 2;
                } 
                
                else {
                    cell._margin = [0, topMargin + 2, 0, 0];
                }
            }
        }
    })

    updateRowSpanCell(maxRowHeight, rowSpanCell);

    if (rowSpanCell.length > 0) 
    {    
        applyVerticalAlignment(node, rowIndex + 1, align, rowSpanCell, manualHeight);
    }
}

let dataLength;
let data;

function printVaiVoltaPDF()
{
    var docDefinition = {
        pageSize: "A4",
        pageMargins: [20, 30],

        info: {
            title: "Lista - Vai e Volta",
            author: "IFNMG - Campus Salinas",
            creator: "CGAE - Listas Eletrônicas",
            producer: "Henrique Cardoso de Souza"
        },

        content: [
            {
                table: {
                    widths: ["auto"],
                    headerRows: 2,

                    body: [
                        [
                            {
                                image: "logo",
                                width: 190,
                                margin: [0, 0, 0, 11]
                            }
                        ],

                        [
                            {
                                text: "CONTROLE DE SAÍDAS DE ALUNOS INTERNOS - VAI E VOLTA",
                                style: "header",
                                margin: [0, 0, 0, 6]
                            }
                        ],

                        [
                            {
                                table: {
                                    widths: [20, 194, 35, 55, 40, 60, 80],
                                    headerRows: 2,
                                    dontBreakRows: true,
                                    keepWithHeaderRows: 1,

                                    body: [
                                        [
                                            {
                                                image: writeRotatedText("QUARTO", "black"),
                                                rowSpan: 2,
                                                fit: [7, 50]
                                            },

                                            {
                                                text: "ASSINATURA DO ALUNO",
                                                style: "tableHeader2",
                                                rowSpan: 2,
                                                margin: [0, 2, 0, 0]
                                            },

                                            {
                                                text: "Nº\nCART.",
                                                style: "tableHeader1",
                                                rowSpan: 2,
                                                margin: [0, 1, 0, 0]
                                            },

                                            {
                                                text: 'SAÍDA',
                                                style: 'tableHeader3',
                                                colSpan: 2
                                            },

                                            {},

                                            {
                                                text: "CHEGADA",
                                                style: "tableHeader3"
                                            },

                                            {
                                                text: "DESTINO",
                                                style: "tableHeader3",
                                                rowSpan: 2,
                                                margin: [0, 2, 0, 0]
                                            },
                                        ],

                                        [
                                            {},
                                            {},
                                            {},

                                            {
                                                text: 'DATA',
                                                style: 'tableHeader1'
                                            },
                                            
                                            {
                                                text: 'HORA',
                                                style: 'tableHeader1'
                                            },

                                            {
                                                text: "HORA",
                                                style: "tableHeader1"
                                            },
                                            
                                            {},
                                        ]
                                    ],
                                
                                    style: "table"
                                },
                            
                                layout: {
                                    hLineWidth: function (i, node) {
                                        return 1;
                                    },
                                    vLineWidth: function (i, node) {
                                        return 1;
                                    },
                                    hLineColor: function (i, node) {
                                        return "#444";
                                    },
                                    vLineColor: function (i, node) {
                                        return "#444";
                                    },
                                    paddingTop: (index, node) => {
                                        applyVerticalAlignment(node, index, 'center', []);
                                        return 0;
                                    },
                                },
                            
                                style: "table"
                            }
                        ]
                    ]
                },

                style: "table",

                layout: {
                    hLineWidth: function (i, node) {
                        return 0;
                    },
                    vLineWidth: function (i, node) {
                        return 0;
                    },
                }
            }
        ],

        styles: {
            header: {
                fontSize: 14,
                bold: true
            },

            simpleText: {
                fontSize: 10,
                bold: false
            },

            table: {
                margin: 0
            },

            tableHeader1: {
                fontSize: 10,
                bold: true
            },

            tableHeader2: {
                fontSize: 12,
                bold: true
            },

            tableHeader3: {
                fontSize: 11,
                bold: true
            }
        },

        images: {
            logo: "IF Logo.png"
        },

        defaultStyle: {
            font: "Arial",
            fontSize: 12,
            alignment: "center"
        }
    };

    for (let i = 0; i < dataLength; i++)
    {
        docDefinition.content[0].table.body[2][0].table.body.push(
            getVaiVoltaData(i)
        );
    }

    pdfMake.fonts = {
        Arial: {
            normal: "arial regular.ttf",
            bold: "arial bold.ttf",
            light: "arial light.ttf",
        },
    };

    pdfMake.createPdf(docDefinition).print();
}

function getVaiVoltaData(index)
{
    let res = [
        {
            text: data[index].quarto,
            style: "simpleText",
            margin: [0, 2, 0, 2]
        },
        
        {
            text: data[index].nome,
            style: "simpleText",
            margin: [0, 2, 0, 2]
        },
        
        {
            text: data[index].idRefeitorio,
            style: "simpleText",
            margin: [0, 2, 0, 2]
        },
        
        {
            text: data[index].data,
            style: "simpleText",
            margin: [0, 2, 0, 2]
        },
        
        {
            text: data[index].horaSaida,
            style: "simpleText",
            margin: [0, 2, 0, 2]
        },
        
        {
            text: data[index].horaChegada,
            style: "simpleText",
            margin: [0, 2, 0, 2]
        },
        
        {
            text: data[index].destino,
            style: "simpleText",
            margin: [0, 2, 0, 2]
        }
    ];

    return res;
}

function printSaidaPDF()
{
    var docDefinition = {
        pageSize: "A4",
        pageMargins: [20, 30],

        info: {
            title: "Lista - Saída",
            author: "IFNMG - Campus Salinas",
            creator: "CGAE - Listas Eletrônicas",
            producer: "Henrique Cardoso de Souza"
        },

        content: [
            {
                table: {
                    widths: ["auto"],
                    headerRows: 2,

                    body: [
                        [
                            {
                                image: "logo",
                                width: 190,
                                margin: [0, 0, 0, 11]
                            }
                        ],

                        [
                            {
                                text: "CONTROLE DE SAÍDAS DE ALUNOS INTERNOS",
                                style: "header",
                                margin: [0, 0, 0, 6]
                            }
                        ],

                        [
                            {
                                table: {
                                    widths: [20, 159, 35, 55, 40, 55, 40, 71],
                                    headerRows: 2,
                                    dontBreakRows: true,
                                    keepWithHeaderRows: 1,

                                    body: [
                                        [
                                            {
                                                image: writeRotatedText("QUARTO", "black"),
                                                fit: [7, 50],
                                                rowSpan: 2,
                                            },

                                            {
                                                text: "ASSINATURA DO ALUNO",
                                                style: "tableHeader2",
                                                rowSpan: 2,
                                                margin: [0, 2, 0, 0]
                                            },

                                            {
                                                text: "Nº\nCART.",
                                                style: "tableHeader1",
                                                rowSpan: 2,
                                                margin: [0, 1, 0, 0]
                                            },

                                            {
                                                text: 'SAÍDA',
                                                style: 'tableHeader3',
                                                colSpan: 2,
                                            },

                                            {},

                                            {
                                                text: "CHEGADA",
                                                style: "tableHeader3",
                                                colSpan: 2,
                                            },

                                            {},

                                            {
                                                text: "DESTINO",
                                                style: "tableHeader3",
                                                rowSpan: 2,
                                                margin: [0, 2, 0, 0]
                                            },
                                        ],

                                        [
                                            {},
                                            {},
                                            {},

                                            {
                                                text: 'DATA',
                                                style: 'tableHeader1',
                                                margin: [0, 4, 0, 0]
                                            },
                                            
                                            {
                                                text: 'HORA',
                                                style: 'tableHeader1',
                                                margin: [0, 4, 0, 0]
                                            },

                                            {
                                                text: "DATA",
                                                style: "tableHeader1",
                                                margin: [0, 4, 0, 0]
                                            },

                                            {
                                                text: "HORA",
                                                style: "tableHeader1",
                                                margin: [0, 4, 0, 0]
                                            },
                                            
                                            {},
                                        ]
                                    ],
                                
                                    style: "table"
                                },
                            
                                layout: {
                                    hLineWidth: function (i, node) {
                                        return 1;
                                    },
                                    vLineWidth: function (i, node) {
                                        return 1;
                                    },
                                    hLineColor: function (i, node) {
                                        return "#444";
                                    },
                                    vLineColor: function (i, node) {
                                        return "#444";
                                    },
                                    paddingTop: (index, node) => {
                                        applyVerticalAlignment(node, index, 'center', []);
                                        return 0;
                                    },
                                },
                            
                                style: "table"
                            }
                        ]
                    ]
                },

                style: "table",

                layout: {
                    hLineWidth: function (i, node) {
                        return 0;
                    },
                    vLineWidth: function (i, node) {
                        return 0;
                    },
                }
            }
        ],

        styles: {
            header: {
                fontSize: 14,
                bold: true
            },

            simpleText: {
                fontSize: 10,
                bold: false
            },

            table: {
                margin: 0
            },

            tableHeader1: {
                fontSize: 10,
                bold: true
            },

            tableHeader2: {
                fontSize: 12,
                bold: true
            },

            tableHeader3: {
                fontSize: 11,
                bold: true
            }
        },

        images: {
            logo: "IF Logo.png"
        },

        defaultStyle: {
            font: "Arial",
            fontSize: 12,
            alignment: "center"
        }
    };

    for (let i = 0; i < dataLength; i++)
    {
        docDefinition.content[0].table.body[2][0].table.body.push(
            getSaidaData(i)
        );
    }

    pdfMake.fonts = {
        Arial: {
            normal: "arial regular.ttf",
            bold: "arial bold.ttf",
            light: "arial light.ttf",
        },
    };

    pdfMake.createPdf(docDefinition).print();
}

function getSaidaData(index)
{
    let res = [
        {
            text: data[index].quarto,
            style: "simpleText",
            margin: [0, 2, 0, 2]
        },
        
        {
            text: data[index].nome,
            style: "simpleText",
            margin: [0, 2, 0, 2]
        },
        
        {
            text: data[index].idRefeitorio,
            style: "simpleText",
            margin: [0, 2, 0, 2]
        },
        
        {
            text: data[index].dataSaida,
            style: "simpleText",
            margin: [0, 2, 0, 2]
        },
        
        {
            text: data[index].horaSaida,
            style: "simpleText",
            margin: [0, 2, 0, 2]
        },
        
        {
            text: data[index].dataChegada,
            style: "simpleText",
            margin: [0, 2, 0, 2]
        },
        
        {
            text: data[index].horaChegada,
            style: "simpleText",
            margin: [0, 2, 0, 2]
        },
        
        {
            text: data[index].destino,
            style: "simpleText",
            margin: [0, 2, 0, 2]
        }
    ];

    return res;
}

function printPernoitePDF()
{
    var docDefinition = {
        pageSize: "A4",
        pageMargins: [20, 30],
        pageOrientation: "landscape",

        info: {
            title: "Lista - Pernoite",
            author: "IFNMG - Campus Salinas",
            creator: "CGAE - Listas Eletrônicas",
            producer: "Henrique Cardoso de Souza"
        },

        content: [
            {
                table: {
                    widths: [220, "*"],
                    headerRows: 1,

                    body: [
                        [
                            {
                                image: "logo",
                                width: 190,
                                margin: [7, 0, 0, 12],
                                alignment: "left"
                            },

                            {
                                text: "CONTROLE DE PERNOITE",
                                style: "header",
                                margin: [85, 57, 0, 0],
                                alignment: "left"
                            }
                        ],

                        [
                            {
                                table: {
                                    widths: [20, 150, 160, 112, 80, 55, 40, 55, 40],
                                    headerRows: 2,
                                    dontBreakRows: true,
                                    keepWithHeaderRows: 1,

                                    body: [
                                        [
                                            {
                                                image: writeRotatedText("QUARTO", "black"),
                                                fit: [7, 50],
                                                rowSpan: 2,
                                            },

                                            {
                                                text: "ALUNO",
                                                style: "tableHeader2",
                                                rowSpan: 2,
                                                margin: [0, 7, 0, 0]
                                            },

                                            {
                                                text: "INFORMAÇÕES DO DESTINO",
                                                style: "tableHeader1",
                                                colSpan: 3
                                            },

                                            {},

                                            {},

                                            {
                                                text: 'SAÍDA',
                                                style: 'tableHeader2',
                                                colSpan: 2,
                                            },

                                            {},

                                            {
                                                text: "CHEGADA",
                                                style: "tableHeader2",
                                                colSpan: 2,
                                            },

                                            {},
                                        ],

                                        [
                                            {},
                                            {},
                                            
                                            {
                                                text: 'ENDEREÇO',
                                                style: 'tableHeader1',
                                                margin: [0, 4, 0, 0]
                                            },
                                            
                                            {
                                                text: 'NOME',
                                                style: 'tableHeader1',
                                                margin: [0, 4, 0, 0]
                                            },
                                            
                                            {
                                                text: 'TELEFONE',
                                                style: 'tableHeader1',
                                                margin: [0, 4, 0, 0]
                                            },

                                            {
                                                text: 'DATA',
                                                style: 'tableHeader1',
                                                margin: [0, 4, 0, 0]
                                            },
                                            
                                            {
                                                text: 'HORA',
                                                style: 'tableHeader1',
                                                margin: [0, 4, 0, 0]
                                            },

                                            {
                                                text: "DATA",
                                                style: "tableHeader1",
                                                margin: [0, 4, 0, 0]
                                            },

                                            {
                                                text: "HORA",
                                                style: "tableHeader1",
                                                margin: [0, 4, 0, 0]
                                            }
                                        ]
                                    ],
                                
                                    style: "table"
                                },
                            
                                layout: {
                                    hLineWidth: function (i, node) {
                                        return 1;
                                    },
                                    vLineWidth: function (i, node) {
                                        return 1;
                                    },
                                    hLineColor: function (i, node) {
                                        return "#444";
                                    },
                                    vLineColor: function (i, node) {
                                        return "#444";
                                    },
                                    paddingTop: (index, node) => {
                                        applyVerticalAlignment(node, index, 'center', []);
                                        return 0;
                                    },
                                },
                            
                                style: "table",
                                colSpan: 2
                            },

                            {}
                        ]
                    ]
                },

                style: "table",

                layout: {
                    hLineWidth: function (i, node) {
                        return 0;
                    },
                    vLineWidth: function (i, node) {
                        return 0;
                    },
                }
            }
        ],

        styles: {
            header: {
                fontSize: 14,
                bold: true
            },

            simpleText: {
                fontSize: 10,
                bold: false
            },

            table: {
                margin: 0
            },

            tableHeader1: {
                fontSize: 10,
                bold: true
            },

            tableHeader2: {
                fontSize: 11,
                bold: true
            }
        },

        images: {
            logo: "IF Logo.png"
        },

        defaultStyle: {
            font: "Arial",
            fontSize: 12,
            alignment: "center"
        }
    };

    for (let i = 0; i < dataLength; i++)
    {
        docDefinition.content[0].table.body[1][0].table.body.push(
            getPernoiteData(i)
        );
    }

    pdfMake.fonts = {
        Arial: {
            normal: "arial regular.ttf",
            bold: "arial bold.ttf",
            light: "arial light.ttf",
        },
    };

    pdfMake.createPdf(docDefinition).print();
}

function getPernoiteData(index)
{
    let res = [
        {
            text: data[index].quarto,
            style: "simpleText",
            margin: [0, 2, 0, 2]
        },
        
        {
            text: data[index].nome,
            style: "simpleText",
            margin: [0, 2, 0, 2]
        },
        
        {
            text: data[index].destino,
            style: "simpleText",
            margin: [0, 2, 0, 2]
        },
        
        {
            text: data[index].responsavel,
            style: "simpleText",
            margin: [0, 2, 0, 2]
        },
        
        {
            text: data[index].telefone,
            style: "simpleText",
            margin: [0, 2, 0, 2]
        },

        {
            text: data[index].dataSaida,
            style: "simpleText",
            margin: [0, 2, 0, 2]
        },
        
        {
            text: data[index].horaSaida,
            style: "simpleText",
            margin: [0, 2, 0, 2]
        },

        {
            text: data[index].dataChegada,
            style: "simpleText",
            margin: [0, 2, 0, 2]
        },
        
        {
            text: data[index].horaChegada,
            style: "simpleText",
            margin: [0, 2, 0, 2]
        }
    ];

    return res;
}

function initialize()
{
    let obj = JSON.parse(localStorage.getItem("data"));
    localStorage.removeItem("data");
    
    try {
        data = obj.dados;
        dataLength = data.length;

        switch (obj.lista)
        {
            case "vai_volta":
                printVaiVoltaPDF();
                break;

            case "saida":
                printSaidaPDF();
                break;

            case "pernoite":
                printPernoitePDF();
                break;
        }

        obj.redirect += "200";
    }

    catch (e) {
        obj.redirect += "503";
    }
    
    finally {
        location.href = obj.redirect;
    }
}

initialize();