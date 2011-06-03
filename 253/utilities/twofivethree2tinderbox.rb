require 'rubygems'
require 'ftools'
require 'json'


$pro_count = []
$con_count = []
$all_links = []
$basic_id = "11111111"
$all_ids = []

PRO_XVAL = -0.5
CON_XVAL = 5

class Link
  attr_accessor :name
  attr_accessor :source
  attr_accessor :destination
  def initialize(name, source, destination)
    @name = name
    @source = source
    @destination = destination
  end
end

def get_tbox_attributes item
  id = $basic_id + item["number"].to_s
  if $all_ids.find{|i| i == id}
    $basic_id = ($basic_id.to_i + 1).to_s
    new_id = $basic_id + item["id"].to_s
    id = new_id
  end
  $all_ids << id
  return {:id => id}
end

def process_item! s
  s.gsub!("<p>", "\n\n")
  s.gsub!("</p>", "")
  s.gsub!("</a>", "")
  s.gsub!("</", "...")
  s.gsub!("<","..")
  s.gsub!(">", ",")
  s.gsub!("&", "&amp;")
end

def item_header item, spaces
  process_item! item["appearance"]
  process_item! item["thinking"]
  process_item! item["inside"]
  tbox_attributes = get_tbox_attributes item
  item["tbox_id"] = tbox_attributes[:id]

  item_string =""
  item_string << spaces + "<item ID='#{tbox_attributes[:id]}' Creator='system' >\n"
  spaces = spaces + "  "
  item_string << spaces + "<attribute name='IsPrototype' >false</attribute>\n"
  item_string << spaces + "<attribute name='Modified' >2011-03-13T17:20:00+00:00</attribute>\n"
  item_string << spaces + "<attribute name='Height'>2</attribute>\n"
  item_string << spaces + "<attribute name='Width'>5</attribute>\n"
  item_string << spaces + "<attribute name='Name'>#{item["name"]}</attribute>\n"
  item_string << spaces + "<attribute name ='Xpos'>#{item["number"]}</attribute>\n"
  item_string << spaces + "<attribute name ='Ypos'>#{item["number"]}</attribute>\n"
#  item_string << spaces + "<attribute name='Appearance' >#{item["appearance"]}</attribute>\n"
#  item_string << spaces + "<attribute name='Inside' >#{item["inside"]}</attribute>\n"
#  item_string << spaces + "<attribute name='Thinking' >#{item["thinking"]}</attribute>\n"
  item_string << spaces + "<attribute name='Number' >#{item["number"]}</attribute>\n"
#  item_string << spaces + "<text></text>\n"
  item_string << spaces + "<text>OUTWARD APPEARANCE:\n#{item["appearance"]}\n\n\nINSIDE INFORMATION:\n#{item["inside"]}\n\n\nWHAT HE IS DOING OR THINKING:\n#{item["thinking"]}</text>\n"
end

def item_footer spaces
  spaces + "</item>\n\n"
end

def level_spaces level
  spaces = "  "
  (1..level).each do |l|
    spaces << "  "
  end
  spaces
end

passengers = JSON.parse(File.open("../data/car2.json").read)

output_xml = ""
passengers.each do |passenger|
  output_xml << item_header(passenger, "    ") + item_footer("    ")
end


#now write to the file
output_file = File.open("253.tbx", "w")
output_file.puts File.open("templates/header.tbx.template").read.gsub!(/\(\(document title\)\)/,"253: Tube Theatre")
output_file.puts output_xml
output_file.puts "</item>"
output_file.puts "<links>"
#$all_links.each do |link|
#  output_file.puts level_spaces(1) + "<link name='#{link.name}' sourceid='#{link.source}' sourcecreator='system' destid='#{link.destination}' destcreator='system' style='0' sstart='-1' slen='0'/>"
#end

output_file.puts File.open("templates/footer.tbx.template").read
