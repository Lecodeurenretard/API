/*A message for the dev:
 * If you have the error message "RangeError: WebAssembly.instantiate(): Out of memory: wasm memory"
 * try connecting in ssh with PuTTY
 */

require('dotenv').config();
const {Client, IntentsBitField, MessageAttachment } = require("discord.js");
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
    console.log(`Connected!`);
});

bot.on('interactionCreate', (interaction) =>{
    if(interaction.isChatInputCommand()){
        let music, options; //il y a une erreur sinn
        switch (interaction.commandName) {
            case 'get_music_file':
                music = interaction.options.get('file').value;
                options =  {
                    hostname: 'api.musiques.nils.test.sc2mnrf0802.universe.wf',
                    path: `/get-req.php?file=${encodeURI(music)}`,
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
                        console.error(res.headers);
                        console.error(res.statusCode);
                        res.on('data', (chunk) => {
                            console.error(chunk);
                            console.log(`\n\n\n\n\n`);
                        });
                        return;
                    }
                    
                    interaction.reply(`http://musiques.nils.test.sc2mnrf0802.universe.wf/api/${encodeURI(music)}`);
                    
                    
                    interaction.channel.send({
                        files: [{
                            attachment: '/home/sc2mnrf0802/nils.test.musiques.wf/api/' + music,
                            name: music //+ '.mp3'
                          }]
                        })                    
                });
                break;

            case "get_music_json":
                music = interaction.options.get('music').value,
                options = {
                    hostname: 'api.musiques.nils.test.sc2mnrf0802.universe.wf',
                    path: `/get-req.php?file=${encodeURI(music)}`,
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
                        console.error(res.headers);
                        console.error(res.statusCode);
                        res.on('data', (chunk) => {
                            console.error(chunk);
                            console.log(`\n\n\n\n\n`);
                        });
                        return;
                    }
                    res.on('data', (chunk) => {
                        interaction.reply(chunk);
                    });
                });
                break;
        
            default:
                interaction.reply("wat? (Unknown command)");
                break;
        }
    }
});

bot.login(process.env.TOKEN);