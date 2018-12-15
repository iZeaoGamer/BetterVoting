# BetterVoting
BetterVoting is a PocketMine-MP plugin for letting players claim their rewards from voting for your server.

## Why BetterVoting over others?
BetterVoting uses your server's api key which means you don't need to download any VRC files. BetterVoting is also an active project which means any issues can be resolved as quickly as possible.

## How to configure BetterVoting?
When you first install BetterVoting, you will get a default config that looks like this:
```yaml
# API Key from https://minecraftpocket-servers.com
api-key: ""
claim:
  # Message Variables:
  # {real-name} - The voter's full username
  # {display-name} - The voter's display name
  # & - Change message color
  broadcast: "{real-name} has voted for free rewards!" # Message to be broadcast when a player votes
  message: "Thanks for voting for out server" # Message to send to the player who voted
  items:
    # Item format: "ItemName:Damage:ICount:CustomName:EnchantName:EnchantLevel"
    # Item IDs are not supported, use the item names instead
    # Replace spaces with "_" for item names
    # For no custom name/default item name, put "default"
    # First 4 fields are required, enchants are optional
    # There is also support for PiggyCustomEnchants, use same format as normal enchants
    # You can have unlimited enchants, just follow the format "EnchantName:EnchantLevel:EnchantName:EnchantLevel" etc
    - "diamond_sword:0:1:Vote Sword:sharpness:5:unbreaking:3"
    - "diamond_pickaxe:0:1:default:efficiency:2:driller:3"
```
The first thing you can see in the config is ``api-key``. This is what BetterVoting uses to connect to the vote website and claim players' votes.
To get your API key, go to [Manage Your Servers](https://minecraftpocket-servers.com/servers/manage/) on minecraftpocket-servers and paste in your API key.
> Notice: As of now, BetterVoting only supports minecraftpocket-servers, and 1 server. Multi website support is a planned feature

Next in the config you will see claim information. The ``broadcast`` key, this is the message that get's broadcasted to the server when a player votes
The ``message`` key is the message sent to the player after they vote

| Variable       | Description               |
|----------------|---------------------------|
| {real-name}    | The voter's full username |
| {display-name} | The voter's display name  |
| &              | Change message color      |

The ``items`` array is the items the player will be given for voting. If you follow the guidelines you shouldn't have any problems.
> Notice: Issues regarding items not working will be closed, as it works fine if you follow the guidelines