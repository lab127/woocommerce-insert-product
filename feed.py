from sys import exit

from inc import cur

def fetch_feed():
        stmt = """
        SELECT p.ID as pid, attachment.ID att_id, p.post_title as the_title, pm.*
        FROM wp_posts p
        INNER JOIN wp_posts attachment ON (attachment.post_parent = p.ID)
        INNER JOIN wp_postmeta pm ON (pm.post_id = attachment.ID)
        WHERE p.post_type = 'product'
        AND pm.meta_key = '_wp_attached_file'
        LIMIT 5
        """

        cur.execute(stmt)
        rows = cur.fetchall()

        if not rows:
                exit("if not rows")

        return rows

print(fetch_feed())