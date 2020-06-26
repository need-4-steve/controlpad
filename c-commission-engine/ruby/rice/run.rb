require './rice_CE'

#####################################
## Make Initial Database conection ##
#####################################
puts "# - Connect to database...\n"
ce = CeMain.new
puts ce.Startup("ce", "root", "53eqRpYtQPP94apf", "127.0.0.1")
#puts ce.Startup("ce", "root", "gypsey", "127.0.0.1")

##################
## Add a System ##
##################
puts "# - Add a System...\n"
sys = CeSystem.new
puts sys.Add(1, "test.game.1", "1", "1", "1", "10", "0", "false", "0")
#"", "", "")

####################
## Add Comm Rules ##
####################
## Add commission rules after system creation ##
rule = CeCommRule.new
## These are the full commission rules needed for each game created ##
rule.Add(2, "1", "1", "1", "", "", "false", "5");
rule.Add(2, "2", "1", "1", "", "", "false", "5");
rule.Add(2, "2", "2", "2", "", "", "false", "2");
rule.Add(2, "3", "1", "1", "", "", "false", "5");
rule.Add(2, "3", "2", "2", "", "", "false", "2");
rule.Add(2, "3", "3", "3", "", "", "false", "2");
rule.Add(2, "4", "1", "1", "", "", "false", "5");
rule.Add(2, "4", "2", "2", "", "", "false", "2");
rule.Add(2, "4", "3", "3", "", "", "false", "2");
rule.Add(2, "4", "4", "4", "", "", "false", "1");

################
## Add a User ##
################
puts "# - Add a User... \n"
user = CeUser.new
puts user.Add(2, "test-1", "1", "1", "2016-10-10", "1")

###################
## Add a Receipt ##
###################
puts "# - Add a Receipt... \n"
receipt = CeReceipt.new
puts receipt.Add(2, "5555555555", "555", "55.55", "2016-9-9", "true");
