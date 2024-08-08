import sqlite3
import pandas as pd
import matplotlib.pyplot as plt

# Database path
db_path = '../data/exports/attempt-24.db'

# SQL query to count the number of rows by day of the week
query = '''
SELECT strftime('%w', ticket_issue_date) AS day_of_week, COUNT(*) AS ticket_count
FROM tickets
GROUP BY day_of_week
ORDER BY day_of_week;
'''

# Connect to the SQLite database
conn = sqlite3.connect(db_path)

# Execute the query and fetch the data into a DataFrame
df = pd.read_sql_query(query, conn)

# Close the database connection
conn.close()

# Map numeric day of the week to names
day_map = {
    '0': 'Sunday',
    '1': 'Monday',
    '2': 'Tuesday',
    '3': 'Wednesday',
    '4': 'Thursday',
    '5': 'Friday',
    '6': 'Saturday'
}

# Convert numeric day of the week to names
df['day_of_week'] = df['day_of_week'].map(day_map)

# Sort DataFrame by the day of the week
df = df.set_index('day_of_week').reindex(['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday']).reset_index()

# Plotting
plt.figure(figsize=(10, 6))
plt.bar(df['day_of_week'], df['ticket_count'], color='skyblue')

# Customizing the plot
plt.title('Number of Tickets Issued by Day of the Week')
plt.xlabel('Day of the Week')
plt.ylabel('Number of Tickets')
plt.xticks(rotation=45)
plt.grid(axis='y', linestyle='--', alpha=0.7)
plt.tight_layout()

# Show the plot
plt.show()
