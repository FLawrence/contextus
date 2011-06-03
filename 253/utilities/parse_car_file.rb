require 'rubygems'
require 'json'

def get_story_data filename
  fulltext = File.open(filename).read
  story_data = {}
  fulltext.gsub!("\r", "")
  fulltext.gsub!("<br>", "")
  fulltext.gsub(/.*?(\<h1\>)(.*?)(\<\/h1\>).*/, "")
  story_data["name"] = $2
  story_data["name"].gsub!(/([0-9][0-9])/, "")
  story_data["number"] = $1

  fulltext.match(/.*?appearance.*?(\<\/h2\>)(.*?)(\<h2\>)/)
  story_data["appearance"] = $2
  fulltext.match(/.*?information.*?(\<\/h2\>)(.*?)(\<h2\>)/)
  story_data["inside"] = $2
  fulltext.match(/.*?thinking.*?(\<\/h2\>)(.*?)(Next passenger)/)
  story_data["thinking"] = $2

  story_data["thinking"].match(/(.*)\<a href.*/)
  story_data["thinking"] = $1
  story_data
end

def story_data_to_csv story_data
  puts story_data["number"] + "," + story_data["name"]
end

car_objects = {}

(38..73).each do |n|
  if(n!=70)
    filename = n.to_s + ".htm"
    car_objects[n] = get_story_data(filename)
#    story_data_to_csv get_story_data(filename)
  end
end

#output all json
puts JSON.pretty_generate(car_objects)
