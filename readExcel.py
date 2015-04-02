import xlrd 
#xlrd.Workbook(encoding = 'utf-8')
book = xlrd.open_workbook("/Users/bernard/Downloads/0326.xls") 

for sheet_name in book.sheet_names(): 
   sheet = book.sheet_by_name(sheet_name) 
   print "============="
   for col_title in sheet.row_values(1):
	   print col_title
	