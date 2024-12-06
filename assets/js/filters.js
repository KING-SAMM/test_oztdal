function communitiesFilter(data, tableHTML) {
        // Start building the HTML as a string
        tableHTML = `
        <table style="
        width: 100%; 
        border-collapse: collapse; 
        font-size: 12pt; 
        font-family: 'Times New Roman', serif; 
        color: black;
        margin: 20px 0;
    ">
        <caption style="
            font-size: 14pt; 
            font-weight: bold; 
            margin-bottom: 10px; 
            text-align: center;
            letter-spacing: 8px;
            font-variant: small-caps;
            color: rgba(120, 120, 120, 1.0);
        ">OZTDAL: All Communities</caption>
        <thead style="
            border-bottom: 2px solid black;
            position: sticky; top: 0;
            max-width: 100vw;
        ">
            <tr class="tableHeader" 
                style="
                    background: white;
                    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);">
                <th style="
                    padding: 8px; 
                    text-align: left; 
                    font-weight: bold; 
                    border-right: 1px solid black;
                    border-bottom: 2px solid black;
                ">Community</th>
                <th style="
                    padding: 8px; 
                    text-align: left; 
                    font-weight: bold; 
                    border-right: 1px solid black;
                    border-bottom: 2px solid black;
                ">Eze</th>
                <th style="
                    padding: 8px; 
                    text-align: left; 
                    font-weight: bold; 
                    border-right: 1px solid black;
                    border-bottom: 2px solid black;
                ">(Lagos) Chairman / President</th>
                <th style="
                    padding: 8px; 
                    text-align: left; 
                    font-weight: bold;
                    border-bottom: 2px solid black;
                ">Local Government</th>
            </tr>
        </thead>
        <tbody>
    `;

    // Loop through the data and add table rows
    data.forEach((community) => {
        tableHTML += `
            <tr style="border-bottom: 1px solid black;">
                <td style="padding: 8px; border-right: 1px solid black;">
                    <a href="http://testoztdal.local/views/community.php?id=${community.community_id || 'N/A'}" class="result-link" style="text-decoration: none;">
                        ${community.name || 'N/A'}
                    </a>
                </td>
                <td style="padding: 8px; border-right: 1px solid black;">${community.eze || 'N/A'}</td>
                <td style="padding: 8px; border-right: 1px solid black;">${community.chair_phone || 'N/A'} ${community.chair_email || 'N/A'}</td>
                <td style="padding: 8px;">${community.local_govt_name || ''}</td>
            </tr>
        `;
    });

    // Close the table
    tableHTML += `</tbody></table>`;

    // Return the full HTML
    return tableHTML;
}

function membersFilter(data) {
    tableHTML = `
        <table style="
            width: 100%; 
            border-collapse: collapse; 
            font-size: 12pt; 
            font-family: 'Times New Roman', serif; 
            color: black;
            margin: 20px 0;
        ">
            <caption style="
                font-size: 14pt; 
                font-weight: bold; 
                margin-bottom: 10px; 
                text-align: center;
                letter-spacing: 8px;
                font-variant: small-caps;
                color: rgba(120, 120, 120, 1.0);
            ">OZTDAL: All Members</caption>
            <thead style="
                border-bottom: 2px solid black;
                position: sticky; top: 0;
                max-width: 100vw;
            ">
                <tr class="tableHeader" 
                    style="
                        background: white;
                        BOX-SHADOW: 0 4px 8px rgba(0, 0, 0, 0.3);">
                    <th style="
                        padding: 8px; 
                        text-align: left; 
                        font-weight: bold; 
                        border-right: 1px solid black;
                        border-bottom: 2px solid black;
                    ">Name</th>
                    <th style="
                        padding: 8px; 
                        text-align: left; 
                        font-weight: bold; 
                        border-right: 1px solid black;
                        border-bottom: 2px solid black;
                    ">Community</th>
                    <th style="
                        padding: 8px; 
                        text-align: left; 
                        font-weight: bold; 
                        border-right: 1px solid black;
                        border-bottom: 2px solid black;
                    ">Local Govt</th>
                    <th style="
                        padding: 8px; 
                        text-align: left; 
                        font-weight: bold;
                        border-bottom: 2px solid black;
                    ">Constituency</th>
                </tr>
            </thead>
            <tbody>
        `;
        data.forEach((item) => {
            tableHTML += `
                <tr style="border-bottom: 1px solid black;">
                    <td style="padding: 8px; border-right: 1px solid black;">${item.firstname || ''} ${item.lastname || ''}</td>
                    <td style="padding: 8px; border-right: 1px solid black;">${item.community_name || ''}</td>
                    <td style="padding: 8px; border-right: 1px solid black;">${item.local_govt || ''}</td>
                    <td style="padding: 8px;">${item.constituency || ''}</td>
                </tr>
            `;
        });
        tableHTML += `</tbody></table>`;
        return tableHTML;
}