require 'csv'

colors = {"Spatial"=>"red", "Interaction"=>"blue", "Incidental"=>"green", "Shared thoughts" => "cyan", "Spatial"=>"yellow", "Relationship"=>"orange", "Explanation"=>"magenta"}

links = CSV.read("253links.csv")
json_links = []
links.each do |link|
  json_links << {:from=>link[0], :to=>link[1], :label=>link[2], :category=>link[3]}
  puts link[0] + " -- " + link[1] + " [color='#{colors[link[3]]}' label='#{link[3]}']"
end

puts "<<<<<<<<<<<<<<<<""

puts JSON.pretty_generate(json_links)
