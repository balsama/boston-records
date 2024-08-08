import sqlite3
import pandas as pd
import matplotlib.pyplot as plt

# Database path
db_path = '../data/exports/attempt-24.db'

# SQL query to select rows where violation_desc_long is 'bike lane'
query = '''
SELECT ticket_issue_date, COUNT(*) AS ticket_count
FROM tickets
WHERE violation_desc_long = 'bike lane'
GROUP BY ticket_issue_date
ORDER BY ticket_issue_date;
'''

# Connect to the SQLite database
conn = sqlite3.connect(db_path)

# Execute the query and fetch the data into a DataFrame
df = pd.read_sql_query(query, conn)

# Close the database connection
conn.close()

# Convert 'ticket_issue_date' to datetime
df['ticket_issue_date'] = pd.to_datetime(df['ticket_issue_date'])

# Plotting
plt.figure(figsize=(14, 8))
plt.plot(df['ticket_issue_date'], df['ticket_count'], marker='o', linestyle='-', color='b')

# Customizing the plot
plt.title('Number of Tickets Issued for Bike Lane Violations Over Time')
plt.xlabel('Date')
plt.ylabel('Number of Tickets')
plt.grid(True)
plt.tight_layout()

# Show the plot
plt.show()
