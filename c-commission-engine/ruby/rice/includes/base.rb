############################
## Simply handling errors ##
############################
def HandleError(json)
	if json['errors']
		puts "\x1b[31m Error: "+json['errors']['detail']
	else
		#if display == false
			puts "\x1b[32m \t Success "
		#else
		#	print "\x1b[32m \t Success: \x1b[34m"
		#	print json
		#	print "\x1b[0m\n"
		#end
	end
	print "\x1b[0m"

	return json
end
