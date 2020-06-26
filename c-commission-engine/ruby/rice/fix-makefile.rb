# Open Makefile
file = File.open("Makefile", "r")
contents = file.read
file.close

# Fix -lpq problem
if contents.include? "/lib -lrice -lpq"
	# Do nothing #
else
	contents = contents.gsub("/lib -lrice", "/lib -lrice -lpq")
	file = File.open("Makefile", "w")
	file.write contents
	file.close
end

# Fix postgresl include folder problem
if contents.include? "-I$(hdrdir) -I$(srcdir) -I/Library/PostgreSQL/9.6/include"
	# Do nothing #
else
	contents = contents.gsub("-I$(hdrdir) -I$(srcdir)", "-I$(hdrdir) -I$(srcdir) -I/Library/PostgreSQL/9.6/include")
	file = File.open("Makefile", "w")
	file.write contents
	file.close
end

# Fix 368 problem 
if contents.include? "ARCH_FLAG =  -arch x86_64"
	# Do nothing #
else
	contents = contents.gsub("ARCH_FLAG =  -arch i386 -arch x86_64", "ARCH_FLAG =  -arch x86_64")
	file = File.open("Makefile", "w")
	file.write contents
	file.close
end
