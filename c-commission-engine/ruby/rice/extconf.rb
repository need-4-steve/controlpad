require 'mkmf-rice'
create_makefile('rice_CE')

# Open Makefile
file = File.open("Makefile", "r")
contents = file.read
file.close

# Write out Makefile
unless contents.include? "/lib -lrice -lpq"
  contents = contents.gsub("/lib -lrice", "/lib -lrice -lpq")
  file = File.open("Makefile", "w")
  file.write contents
  file.close
end
