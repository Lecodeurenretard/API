/*A message for the dev:
 * If you have the error message "RangeError: WebAssembly.instantiate(): Out of memory: wasm memory"
 * try connecting in ssh with PuTTY
 */

require('dotenv').config();
const {Client, IntentsBitField, MessageAttachment, EmbedBuilder } = require("discord.js");
//const { Message } = require('@discordjs/builders');  don't work
const http = require('node:http');

const bot  = new Client({
    intents: [
        IntentsBitField.Flags.Guilds,
        IntentsBitField.Flags.GuildMembers,
        IntentsBitField.Flags.GuildMessages,
        IntentsBitField.Flags.MessageContent
    ]
});

bot.on('ready', async (c) => {
    console.log('Connected!');
});

bot.on('interactionCreate', (interaction) =>{
    if(interaction.isChatInputCommand()){
        let music, options; //il y a une erreur sinn
        switch (interaction.commandName) {
            case 'get_music_file':
                music = interaction.options.get('file').value;
                options =  {
                    hostname: 'api.musiques.nils.test.sc2mnrf0802.universe.wf',
                    path: `/get-json.php?file=${encodeURI(music)}`,
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json'
                    }
                };

                http.get(options, async (res) => {
                    res.setEncoding('utf-8');
                    const { statusCode } = res;
                    //handling errors
                    if(statusCode == 404){
                        interaction.reply("Oops, it seem that this file doesn't exists!");
                        return;
                    }else if(statusCode >= 500){
                         interaction.reply(`An error occured, please contact @le_discodeur (HTTP error code ${statusCode})`);
                         return;
                    }else if(statusCode >= 400){
                        interaction.reply(`Something's wrong, please try again`);
                        return;
                    }
                    
                    if(!music.endsWith('.mp3')){music += '.mp3';}
                    
                    await interaction.reply(`http://musiques.nils.test.sc2mnrf0802.universe.wf/api/${encodeURI(music)}`);
                    interaction.followUp({
                        files: [{
                            attachment: '/home/sc2mnrf0802/nils.test.musiques.wf/api/' + music,
                            name: music
                        }]
                    }) ;

                });
                break;

            case "get_music_json":
                music = interaction.options.get('music').value,
                options = {
                    hostname: 'api.musiques.nils.test.sc2mnrf0802.universe.wf',
                    path: `/get-json.php?file=${encodeURI(music)}`,
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json'
                    }
                };

                http.get(options, (res) => {
                    res.setEncoding('utf-8');
                    if(res.statusCode === 404){
                        interaction.reply("Sorry, but this file don't exist, try again!");
                        console.error(`The user @${interaction.user.username} tried to access the file "${music}" which didn't exist.\n`);
                        return;
                    }else if(res.statusCode >= 400){
                        interaction.reply("Oops, looks like something went wrong. Try again with another parameter.");
                        
                        console.error("Error: ");
                        console.error("Code: ", res.statusCode);
                        console.error("Headers: \n"+res.headers);
                        
                        res.on('data', (chunk) => {
                            console.error(chunk);
                            console.log(`\n\n\n\n\n`);
                        });
                        return;
                    }
                    res.on('data', (chunk) => {
                        const response = JSON.parse(chunk);
                        let composers = '';
                        response.composers.forEach((artist) => {composers += artist + ', '});  //'artist, another one, ANOTHER ONE!, '
                        composers = composers.slice(0, -2); // remove the last ', '
                        

                        let embed = new EmbedBuilder()
                            .setTitle(`informations about ${music}`)
                            .setURL(`http://api.musiques.nils.test.sc2mnrf0802.universe.wf/get-json.php?file=${encodeURI(music)}`)
                            .setDescription("Here is the requested music's infos.")
                            .addFields(
                                { name: "title", value: unknown(response.title) },
                                { name: "track", value: unknown(response.track).toString() },
                                { name: "album", value: unknown(response.album) },
                                { name: "artists", value: unknown(composers) },
                            );
                        if(!response.commentaire){embed = embed.addFields({ name: "description", value: response.commentaire });}
                        interaction.reply({embeds: [embed]});
                    });
                });
                break;
        
            default:
                interaction.reply("wat? (Unknown command)");
                break;
        }
    }
});

function unknown(toVerif){
    //console.log(toVerif);console.log('"'+(toVerif? toVerif : 'unknown') +'"');
    return (toVerif || toVerif===0)? toVerif : 'unknown';    //checks if toVerif is set
}

bot.login(process.env.TOKEN);