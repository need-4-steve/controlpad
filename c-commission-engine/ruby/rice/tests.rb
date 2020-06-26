require './rice_CE'
require 'json'
require './includes/base'

#################
## Debug Level ##
#################
#define DEBUG_ERROR			1
#define DEBUG_WARN			2
#define DEBUG_INFO			3
#define DEBUG_DEBUG			4
#define DEBUG_TRACE			5
#define DEBUG_SQL			6
#define DEBUG_NETWORK_IN	7
#define DEBUG_NETWORK_OUT	8
#define DEBUG_OFF			9

###################
## Debug Display ##
###################
#define DEBUG_SCREEN		1
#define	DEBUG_FILE			2
#define DEBUG_BOTH			3

## Predefined values to make test unique ##
random = rand(9999999)
#puts "random = %d" %random

#####################################
## Make Initial Database conection ##
#####################################
puts "\x1b[33m#################### Database Connection ####################\x1b[0m"
print "Connect to database... \t"
ce = CeMain.new

##########################
## Set the debug values ##
##########################
ce.SetDebugDisplay(1);
ce.SetDebugLevel(1); ## 1 - only errors ##

#if ce.Startup2() == false # still testing 
if ce.Startup("ce", "root", "53eqRpYtQPP94apf", "127.0.0.1") == false
#if ce.Startup("ce", "root", "gypsey", "127.0.0.1") == false
	puts "\x1b[31m Error: Startup Database Error"
else
	puts "\x1b[32m Success"
end
print "\x1b[0m"

#################
## Init Tables ##
#################
puts "\x1b[33m#################### Init Tables ####################\x1b[0m"
print "Init Tables... \t"
if ce.InitTables("TEST") == false
	puts "\x1b[31m Error"
else
	puts "\x1b[32m Success"
end 
print "\x1b[0m"

############################################################################################
######################################### System Users #####################################
############################################################################################

puts "\x1b[33m#################### System Users ####################\x1b[0m"

print "Add a SystemUser... "
sys = CeSystemUser.new
email = 'tests%d@test.com' %random
json = HandleError JSON.parse sys.Add(email, 'TestPas$&1234')
sysuser_id = json["systemuser"].first["id"]

print "Edit a SystemUser... "
email = 'mytests%d@test.com' %random
HandleError JSON.parse sys.Edit(sysuser_id.to_i, sysuser_id.to_i, email, 'TestPas$&1234', "127.0.0.1")

print "Query SystemUser... "
HandleError JSON.parse sys.Query();

print "Disable SystemUser... "
HandleError JSON.parse sys.Disable(1, sysuser_id.to_i);

print "Enable SystemUser... "
HandleError JSON.parse sys.Enable(1, sysuser_id.to_i);

############################################################################################
########################################### Systems ########################################
############################################################################################

puts "\x1b[33m#################### Systems ####################\x1b[0m"

print "Add a System... "
sys = CeSystem.new
gamename = 'test.game.%d' %random
json = HandleError JSON.parse sys.Add(1, gamename, "1", "0", "1", "10", "0", "false", "0")
system_id = json["system"].first["id"]

print "Edit a System... "
json = HandleError JSON.parse sys.Edit(1, gamename, "1", "0", "1", "10", "0", "false", "0")

print "Query System... "
json = HandleError JSON.parse sys.Query(1)

print "Disable a System... "
json = HandleError JSON.parse sys.Disable(system_id.to_i)

print "Enable a System... "
json = HandleError JSON.parse sys.Enable(system_id.to_i)

#####################################################################################
######################################### Users #####################################
#####################################################################################

puts "\x1b[33m#################### Users ####################\x1b[0m"

user = CeUser.new
sponsor_id = "sponsor-1"
user_id = "user-1"
sponsor_2 = "sponsor-2"
user_2 = "user-2"
print "Add a Sponsor...    "
HandleError JSON.parse user.Add(system_id.to_i, sponsor_id, "0", "0", "2016-10-10", "1")
print "Add a Sponsor-2...    "
HandleError JSON.parse user.Add(system_id.to_i, sponsor_2, "0", "0", "2016-10-10", "1")
print "Add a User...    "
HandleError JSON.parse user.Add(system_id.to_i, user_id, sponsor_id, sponsor_id, "2016-10-10", "1")
print "Add a User-2...    "
HandleError JSON.parse user.Add(system_id.to_i, user_2, sponsor_2, sponsor_2, "2016-10-10", "1")

print "Edit a User... "
HandleError JSON.parse user.Edit(system_id.to_i, user_id, sponsor_id, sponsor_id, "2016-10-15", "1")

print "Query Users... "
HandleError JSON.parse user.Query(system_id.to_i)

print "Disable a User... "
HandleError JSON.parse user.Disable(system_id.to_i, user_id)

print "Enable a User... "
HandleError JSON.parse user.Enable(system_id.to_i, user_id)

#####################################################################################
####################################### Receipts ####################################
#####################################################################################

puts "\x1b[33m#################### Receipts ####################\x1b[0m"

print "Add a Receipt... "
receipt = CeReceipt.new
receipt_id = "%d" %random
HandleError JSON.parse receipt.Add(system_id.to_i, receipt_id, user_id, "500.55", "2016-10-9", "true");

print "Add a Receipt-2... "
HandleError JSON.parse receipt.Add(system_id.to_i, receipt_id+"1", user_2, "1992.22", "2016-10-9", "true");

print "Edit a Receipt... "
HandleError JSON.parse receipt.Edit(system_id.to_i, receipt_id, user_id, "500.99", "2016-10-10", "true");

print "Query Receipts... "
HandleError JSON.parse receipt.Query(system_id.to_i, "2016-10-1", "2016-10-30");

print "Disable a Receipt... "
HandleError JSON.parse receipt.Disable(system_id.to_i, receipt_id);

print "Enable a Receipt... "
HandleError JSON.parse receipt.Enable(system_id.to_i, receipt_id);

#####################################################################################
####################################### RankRules ###################################
#####################################################################################

puts "\x1b[33m#################### RankRules ####################\x1b[0m"

print "Add a RankRule... "
rankrule = CeRankRule.new
json = HandleError JSON.parse rankrule.Add(system_id.to_i, "1", "1", "0", "0", "false", "0", "0");
rankrule_id = json['rankrule'].first['id'];

print "Edit a RankRule... "
json = HandleError JSON.parse rankrule.Edit(system_id.to_i, rankrule_id, "1", "1", "0", "0", "false", "0", "0");

print "Query RankRules... "
json = HandleError JSON.parse rankrule.Query(system_id.to_i);

print "Disable a RankRule... "
json = HandleError JSON.parse rankrule.Disable(system_id.to_i, rankrule_id);

print "Enable a RankRule... "
json = HandleError JSON.parse rankrule.Enable(system_id.to_i, rankrule_id);

#####################################################################################
###################################### Comm Rules ###################################
#####################################################################################

puts "\x1b[33m#################### Comm Rules ####################\x1b[0m"

print "Add a CommRule... "
rule = CeCommRule.new
json = HandleError JSON.parse rule.Add(system_id.to_i, "1", "1", "1", "", "", "false", "5");
commrule_id = json['commrule'].first['id']

print "Edit a CommRule... "
json = HandleError JSON.parse rule.Edit(system_id.to_i, commrule_id, "1", "1", "1", "", "", "false", "5");

print "Query CommRules... "
json = HandleError JSON.parse rule.Query(system_id.to_i);

print "Disable a CommRule... "
json = HandleError JSON.parse rule.Disable(system_id.to_i, commrule_id);

print "Enable a CommRule... "
json = HandleError JSON.parse rule.Enable(system_id.to_i, commrule_id);

#####################################################################################
##################################### CMComm Rules ##################################
#####################################################################################

puts "\x1b[33m#################### CMComm Rules ####################\x1b[0m"

print "Add a CMCommRule... "
cmrule = CeCMCommRule.new
json = HandleError JSON.parse cmrule.Add(system_id.to_i, "1", "1", "1", "5");
cmcommrule_id = json['cmcommrule'].first['id']

print "Edit a CMCommRule... "
json = HandleError JSON.parse cmrule.Edit(system_id.to_i, cmcommrule_id, "1", "1", "1", "5");

print "Query CMCommRule... "
json = HandleError JSON.parse cmrule.Query(system_id.to_i);

print "Disable CMCommRule... "
json = HandleError JSON.parse cmrule.Disable(system_id.to_i, cmcommrule_id);

print "Enable CMCommRule... "
json = HandleError JSON.parse cmrule.Enable(system_id.to_i, cmcommrule_id);

#####################################################################################
####################################### Pool Pots ###################################
#####################################################################################

puts "\x1b[33m#################### Pool Pots ####################\x1b[0m"

print "Add a PoolPot... "
poolpot = CePoolPot.new
json = HandleError JSON.parse poolpot.Add(system_id.to_i, "5000", "1", "2016-10-1", "2016-10-31");
poolpot_id = json['poolpot'].first['id']

print "Edit a PoolPot... "
json = HandleError JSON.parse poolpot.Edit(system_id.to_i, poolpot_id, "7777", "1", "2016-10-1", "2016-10-31");

print "Query PoolPots... "
json = HandleError JSON.parse poolpot.Query(system_id.to_i);

print "Disable a PoolPots... "
json = HandleError JSON.parse poolpot.Disable(system_id.to_i, poolpot_id);

print "Enable a PoolPots... "
json = HandleError JSON.parse poolpot.Enable(system_id.to_i, poolpot_id);

#####################################################################################
###################################### Pool Rules ###################################
#####################################################################################

puts "\x1b[33m#################### Pool Rules ####################\x1b[0m"

print "Add a PoolRule... "
poolrule = CePoolRule.new
json = HandleError JSON.parse poolrule.Add(system_id.to_i, poolpot_id, "1", "4", "5");
poolrule_id = json['poolrule'].first['id']

print "Edit a PoolRule... "
json = HandleError JSON.parse poolrule.Edit(system_id.to_i, poolrule_id, "1", "4", "10");

print "Query PoolRules... "
json = HandleError JSON.parse poolrule.Query(system_id.to_i, poolpot_id);

print "Disable a PoolRule... "
json = HandleError JSON.parse poolrule.Disable(system_id.to_i, poolrule_id);

print "Enable a PoolRule... "
json = HandleError JSON.parse poolrule.Enable(system_id.to_i, poolrule_id);

puts "\x1b[33m#################### Calc Pool ####################\x1b[0m"

print "Run a Pool... \t"
json = HandleError JSON.parse poolpot.RunPool(system_id.to_i, poolpot_id);

###################################################################################
####################################### Bonus #####################################
###################################################################################

puts "\x1b[33m#################### Bonus ####################\x1b[0m"

print "Add a Bonus... "
bonus = CeBonus.new

json = HandleError JSON.parse bonus.Add(system_id.to_i, "99", "500.49", "2016-11-29");
bonus_id = json['bonus'].first['id']

print "Edit a Bonus... "
json = HandleError JSON.parse bonus.Edit(system_id.to_i, bonus_id, "98", "500.50", "2016-11-30");

print "Query Bonus... "
json = HandleError JSON.parse bonus.Query(system_id.to_i);

print "Query User Bonus... "
json = HandleError JSON.parse bonus.QueryUser(system_id.to_i, "98");

print "Disable a Bonus... "
json = HandleError JSON.parse bonus.Disable(system_id.to_i, bonus_id);

print "Enable a Bonus... "
json = HandleError JSON.parse bonus.Enable(system_id.to_i, bonus_id);

#####################################################################################
##################################### Commissions ###################################
#####################################################################################

puts "\x1b[33m#################### Commissions ####################\x1b[0m"

# This returns all user payouts #
print "Run a Prediction... "
commission = CeCommission.new
json = HandleError JSON.parse commission.Predict(system_id.to_i, "2016-10-1", "2016-10-31");
if json['payouts'].first['commission'] != "25.05"
	puts "\x1b[31mError: Commission Incorrect = "+json['payouts'].first['commission']+" \x1b[0m"
end

# This returns only sum total receipts, commissions and achievement bonuses #
print "Predict GrandTotal... "
json = HandleError JSON.parse commission.PredictGrandTotal(system_id.to_i, "2016-10-1", "2016-10-31");
if json['grandpayouts']['receipts'] != "2493.21"
	puts "\x1b[31mError: GrandPayouts Receipts Incorrect = "+json['grandpayouts']['receipts']+" \x1b[0m"
end
if json['grandpayouts']['commissions'] != "124.66"
	puts "\x1b[31mError: GrandPayouts Commission Incorrect = "+json['grandpayouts']['commissions']+" \x1b[0m"
end
if json['grandpayouts']['achvbonuses'] != "0.00"
	puts "\x1b[31mError: GrandPayouts AchvBonus Incorrect = "+json['grandpayouts']['achvbonuses']+" \x1b[0m"
end

# This returns only sum total receipts, commissions and achievement bonuses #
print "Calc Commission... "
json = HandleError JSON.parse commission.Calc(system_id.to_i, "2016-10-1", "2016-10-31");
if json['grandpayouts']['receipts'] != "2493.21"
	puts "\x1b[31mError: Calc Receipts Incorrect = "+json['grandpayouts']['receipts']+" \x1b[0m"
end
if json['grandpayouts']['commissions'] != "124.66"
	puts "\x1b[31mError: Calc Commissions Incorrect = "+json['grandpayouts']['commissions']+" \x1b[0m"
end
if json['grandpayouts']['achvbonuses'] != "0.00"
	puts "\x1b[31mError: Calc Achvbonus Incorrect = "+json['grandpayouts']['achvbonuses']+" \x1b[0m"
end

print "Query Batches... "
json = HandleError JSON.parse commission.QueryBatches(system_id.to_i);
batch_id = json['batches'].last['id']

#print "Query User... \t "
#json = HandleError JSON.parse commission.QueryUser(system_id.to_i, sponsor_id);
#if json['commission'].first['amount'] != "25.0495"
#	puts "\x1b[31mError: Query User Amount Incorrect = "+json['commission'].first['amount']+" \x1b[0m"
#end

#print "Query Comm... \t "
#json = HandleError JSON.parse commission.QueryComm(system_id.to_i, batch_id);
#if json['commissions'].first['amount'] != "25.0495"
#	puts "\x1b[31mError: Query User Amount Incorrect = "+json['commissions'].first['amount']+" \x1b[0m"
#end

print "SetRankOverride... \t "
json = HandleError JSON.parse commission.SetRankOverride(4);
#puts json

print "FullCalc... \t "
json = HandleError JSON.parse commission.FullCalc("2016-11-1", "2016-11-30");
#puts json

#####################################################################################
####################################### Payouts #####################################
#####################################################################################

puts "\x1b[33m#################### Payouts ####################\x1b[0m"

# This returns all user payouts #
#print "Payout Query... "
payout = CePayout.new
json = HandleError JSON.parse payout.Query(system_id.to_i, "false");
grandtotal_id = json['grandtotals'].first['id']

print "Payout Auth... "
json = HandleError JSON.parse payout.Auth(system_id.to_i, grandtotal_id, "true");

print "Payout AuthBulk... "
json = HandleError JSON.parse payout.AuthBulk(system_id.to_i);

print "Payout Disable... "
json = HandleError JSON.parse payout.Disable(system_id.to_i, grandtotal_id);

print "Payout Enable... "
json = HandleError JSON.parse payout.Enable(system_id.to_i, grandtotal_id);

#####################################################################################
#################################### Bank Account ###################################
#####################################################################################

puts "\x1b[33m#################### Bank Account ####################\x1b[0m"
    
print "Add BankAccount... "
bankaccount = CeBankAccount.new
json = HandleError JSON.parse bankaccount.Add(system_id.to_i, sponsor_id, "1", "11111111", "5555555555", "Ponce De Leon");

print "Edit BankAccount... "
json = HandleError JSON.parse bankaccount.Edit(system_id.to_i, sponsor_id, "1", "21111112", "4555555554", "Ponce De Leon The Seco");

print "Query BankAccounts... "
json = HandleError JSON.parse bankaccount.Query(system_id.to_i);

## Disable wipes out bank information ##
print "Disable BankAccount.. "
json = HandleError JSON.parse bankaccount.Disable(system_id.to_i, sponsor_id);

print "Enable BankAccount... "
json = HandleError JSON.parse bankaccount.Enable(system_id.to_i, sponsor_id);

print "InitiateValidation... "
json = HandleError JSON.parse bankaccount.InitiateValidation(system_id.to_i, sponsor_id);
amount1 = json['validation']['amount1'];
amount2 = json['validation']['amount2'];

print "Validate... \t"
json = HandleError JSON.parse bankaccount.Validate(system_id.to_i, sponsor_id, amount1, amount2);

## Make sure we have bank account info to process with ##
print "Edit BankAccount... "
json = HandleError JSON.parse bankaccount.Edit(system_id.to_i, sponsor_id, "1", "21111112", "4555555554", "Ponce De Leon The Seco");

#####################################################################################
###################################### Payments #####################################
#####################################################################################

puts "\x1b[33m#################### Payments ####################\x1b[0m"

print "Set Payment Type... "
payment = CePayment.new
json = HandleError JSON.parse payment.SetPaymentType(system_id.to_i, 2); # 2 is local Nacha

print "Process Payments... "
json = HandleError JSON.parse payment.Process(system_id.to_i, batch_id);

print "Query User... \t"
json = HandleError JSON.parse payment.QueryUser(system_id.to_i, sponsor_id);

print "Query Batch... "
json = HandleError JSON.parse payment.QueryBatch(system_id.to_i, batch_id);

print "Query NoPay... \t"
json = HandleError JSON.parse payment.QueryNoPay(system_id.to_i, batch_id);

#################
## Drop Tables ##
#################
puts "\x1b[33m#################### Drop Tables ####################\x1b[0m"

print "Drop Tables... \t"
if ce.DropTables() == false
	puts "\x1b[31m Error"
else
	puts "\x1b[32m Success"
end
print "\x1b[0m"
