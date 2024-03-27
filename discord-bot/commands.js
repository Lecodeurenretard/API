require('dotenv').config();
const { REST, Routes, ApplicationCommandOptionType } = require("discord.js");

const commands = [
    {
        name: "get-infos",
        description: "Fetch the music's infos in the JSON format",
        options: [
            {
                name: 'music',
                description: 'The name of the file of the music',
                type: ApplicationCommandOptionType.String,
                required: true
            }
        ]
    },
    {
        name: 'get_music',
        description: "Fetch the music's file and give the link to the ressource",
        options : [
            {
                name: 'file',
                description: 'the name of the file to fetch',
                type: ApplicationCommandOptionType.String,
                required: true
            }
        ]
    }
];

const rest = new REST({version: 10}).setToken(process.env.TOKEN);

(async () => {
    console.log("Registering commands...");
    try {
        await rest.put(
            Routes.applicationGuildCommands(process.env.THIS_BOT_ID, process.env.NMT_STUDIO_ID),
            {body: commands}
        );
        console.log("Done!");
    } catch (error) {
        console.error(`An error occured: ${error.message}`);
    }
})();