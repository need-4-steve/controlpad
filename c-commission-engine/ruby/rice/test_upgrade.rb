require './rice_CE'
require 'json'

ce = CeMain.new
comm = ce.Startup("united-sim1");

###########
## Users ##
###########
puts "# - Add a User... \n"
user = CeUser.new
puts user.Add(1, "test-1B", "1", "1", "2016-10-10", "1")

puts "# - Edit a User... \n"
puts user.Edit(1, "test-1B", "1", "1", "2016-11-11", "1")

puts "# - Disable a User... \n"
puts user.Disable(1, "test-1B")

puts "# - Enable a User... \n"
puts user.Enable(1, "test-1B")

##############
## Receipts ##
##############
puts "# - Add a Receipt... \n"
receipt = CeReceipt.new
puts receipt.Add(1, "9999999", "1", "100.10", "0.00", "2018-5-1", "", "5", "true")

puts "# - Edit a Receipt... \n"
puts receipt.Edit(1, "9999999", "1", "111.11", "0.00", "2018-5-3", "", "5", "true")

puts "# - Disable a Receipt... \n"
puts receipt.Disable(1, "5")

puts "# - Enable a Receipt... \n"
puts receipt.Enable(1, "5")